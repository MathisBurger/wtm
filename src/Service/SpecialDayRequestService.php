<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\SpecialDayRequest;
use App\Entity\WorktimeSpecialDay;
use App\Repository\EmployeeRepository;
use App\Repository\SpecialDayRequestRepository;
use App\RestApi\HolidayApiFactory;
use App\RestApi\HolidayApiInterface;
use App\Voter\SpecialDayRequestVoter;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Service handling special day requests
 */
class SpecialDayRequestService
{

    private HolidayApiInterface $holidayApi;

    public function __construct(
        private readonly SpecialDayRequestRepository $requestRepository,
        private readonly EmployeeRepository $employeeRepository,
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly SluggerInterface $slugger,
        private readonly KernelInterface $kernel,
        private readonly WorktimeSpecialDayService $worktimeSpecialDayService,
        private readonly MailService $mailService
    ) {
        $this->holidayApi = HolidayApiFactory::create();
    }

    /**
     * Gets all special day requests
     *
     * @return array All requests
     */
    public function getRequests(): array
    {
        return $this->requestRepository->findAll();
    }

    /**
     * Gets a special day request
     *
     * @param int $id The ID
     * @return SpecialDayRequest|null The request
     */
    public function getRequest(int $id): ?SpecialDayRequest
    {
        return $this->requestRepository->find($id);
    }

    /**
     * Creates a special day request
     *
     * @param FormInterface $form The form
     * @return void
     * @throws Exception Thrown on error
     */
    public function addSpecialDayRequest(FormInterface $form): void
    {
        $employee = $this->employeeRepository->findOneBy(['username' => $this->security->getUser()->getUserIdentifier()]);
        if (!$employee) {
            throw new Exception(
                $this->translator->trans('messages.userDoesNotExist')
            );
        }
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new Exception(
                $this->translator->trans('error.invalidForm')
            );
        }
        /** @var array $formData */
        $formData = $form->getData();
        $this->checkHolidayLogic($employee, $formData);
        $request = new SpecialDayRequest();
        $request->setEmployee($employee);
        $request->setStartDate($formData['startDate']);
        if (isset($formData['isMuliDay']) && $formData['isMuliDay']) {
            $request->setEndDate($formData['endDate']);
        } else {
            $request->setEndDate($formData['startDate']);
        }
        if (isset($formData['email'])) {
            $request->setRespondEmail($formData['email']);
        }
        $request->setNotes($formData['notes']);
        $request->setReason($formData['reason']);

        $request->setDocumentFileName($this->processFile($formData));
        $employee->addSpecialDayRequest($request);
        $this->entityManager->persist($request);
        $this->entityManager->persist($employee);
        $this->entityManager->flush();
    }

    /**
     * Gets all personal requests
     *
     * @return array All personal requests
     */
    public function getPersonalRequests(): array
    {
        return $this->employeeRepository->findOneBy(['username' => $this->security->getUser()->getUserIdentifier()])->getSpecialDayRequests()->toArray();
    }


    /**
     * Gets the file path to download file
     *
     * @param int $id The ID of the request
     * @return string|null The final path
     */
    public function downloadFile(int $id): ?string
    {
        $request = $this->requestRepository->find($id);
        if (!$this->security->isGranted(SpecialDayRequestVoter::READ, $request)) {
            throw new AccessDeniedException();
        }
        if ($request->getDocumentFileName() === null) {
            throw new AccessDeniedException();
        }
        return $request->getDocumentFileName();
    }

    /**
     * Handles special day requests
     *
     * @param int $id The ID of the request
     * @param string $action The action that should be performed
     * @return void
     * @throws Exception
     */
    public function handleSpecialDayRequest(int $id, string $action)
    {
        $request = $this->requestRepository->find($id);
        if ($request === null) {
            return;
        }
        if ($action === 'accept') {
            $formData = [
                'isMultiDay' => $request->getStartDateString() !== $request->getEndDateString(),
                'startDate' => $request->getStartDate(),
                'endDate' => $request->getEndDate(),
                'reason' => $request->getReason(),
                'notes' => $request->getNotes()
            ];
            $this->worktimeSpecialDayService->createSpecialDays($request->getEmployee()->getId(), null, $formData);
            $this->mailService->sendRequestHandleMail($request, $action);
            $this->entityManager->remove($request);
            $this->entityManager->flush();
        } else if ($action === 'deny') {
            $this->mailService->sendRequestHandleMail($request, $action);
            $this->entityManager->remove($request);
            $this->entityManager->flush();
        }
    }

    /**
     * Processes the file from the form
     *
     * @param array $formData The form data
     * @return string|null The processed file path of the file
     */
    private function processFile(array $formData): ?string
    {
        if (isset($formData['documentFile']) && $formData['documentFile']) {
            /** @var UploadedFile $file */
            $file = $formData['documentFile'];
            if ($file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalName);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
                try {
                    $documentsDir = $this->kernel->getContainer()->getParameter('documents_directory');
                    $newFile = $file->move($documentsDir, $newFilename);
                    return $newFile->getRealPath();
                } catch (FileException $e) {
                    return null;
                }
            }
        }
        return null;
    }

    /**
     * Checks for the holiday logic. An exception is thrown on invalid holiday amount.
     * NOTE: Logic is copied from the worktime special day service. It is not refactored, because
     * it would take too much time and time is money.
     *
     * @param Employee $employee The employee
     * @return void
     * @throws Exception The exception thrown
     */
    private function checkHolidayLogic(Employee $employee, array $formData)
    {
        $existingHolidays = $employee->getWorktimeSpecialDays()
            ->filter(
                fn (WorktimeSpecialDay $d) =>
                    $d->getDate()->format("Y") === (new DateTime())->format("Y")
                    && $d->getReason() === WorktimeSpecialDay::REASON_HOLIDAY
            );
        if (isset($formData['isMuliDay']) && $formData['isMuliDay']) {
            $holidays = $this->holidayApi->getWithoutHolidays($formData['startDate'], $formData['endDate']);
            if ($formData['reason'] === WorktimeSpecialDay::REASON_HOLIDAY) {
                $thisYearHolidays = array_filter($holidays, function (DateTimeInterface $item) {
                    return $item->format('Y') === (new DateTime())->format('Y');
                });
                if ($employee->getHolidays() - $existingHolidays->count() - count($thisYearHolidays) < 0) {
                    throw new Exception("Zu viele Urlaubstage.");
                }
            }
            return;
        }
        if (
            $formData['reason'] === WorktimeSpecialDay::REASON_HOLIDAY
            && $formData['startDate']->format("Y") === (new DateTime())->format("Y")
            && $employee->getHolidays() - $existingHolidays->count() <= 0
        ) {
            throw new Exception(
                $this->translator->trans('error.tooFewHolidays')
            );
        }
    }

}