<?php

namespace App\Service;

use App\Entity\WorktimeSpecialDay;
use App\Repository\EmployeeRepository;
use App\Repository\WorktimeSpecialDayRepository;
use App\RestApi\HolidayApiFactory;
use App\RestApi\HolidayApiInterface;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WorktimeSpecialDayService
{

    private HolidayApiInterface $holidayApi;

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly WorktimeSpecialDayRepository $worktimeSpecialDayRepository,
        private readonly TranslatorInterface $translator
    ){
        $this->holidayApi = HolidayApiFactory::create();
    }

    /**
     * Creates special days
     *
     * @param int $id The ID of the employee
     * @param FormInterface $form The form containing the data
     * @return void
     * @throws Exception The exception thrown on error
     */
    public function createSpecialDays(int $id, FormInterface $form): void
    {
        $employee = $this->employeeRepository->find($id);
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
        $formData = $form->getData();
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
            foreach ($holidays as $holiday) {
                $day = new WorktimeSpecialDay();
                $day->setEmployee($employee);
                $day->setDate($holiday);
                $day->setNotes($formData['notes']);
                $day->setReason($formData['reason']);
                $employee->addWorktimeSpecialDay($day);
                $this->entityManager->persist($day);
            }
            $this->entityManager->persist($employee);
            $this->entityManager->flush();
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
        $day = new WorktimeSpecialDay();
        $day->setEmployee($employee);
        $day->setDate($formData['startDate']);
        $day->setNotes($formData['notes']);
        $day->setReason($formData['reason']);
        $employee->addWorktimeSpecialDay($day);
        $this->entityManager->persist($day);
        $this->entityManager->persist($employee);
        $this->entityManager->flush();
    }

    /**
     * Deletes a special day
     *
     * @param int $id The ID of the special day
     * @return int The ID of the employee
     * @throws Exception
     */
    public function deleteSpecialDays(int $id): int
    {
        $day = $this->worktimeSpecialDayRepository->find($id);
        if (!$day) {
            throw new Exception(
                $this->translator->trans('error.specialDayDoesNotExist')
            );
        }
        $day->getEmployee()->removeWorktimeSpecialDay($day);
        $this->entityManager->persist($day->getEmployee());
        $this->entityManager->remove($day);
        $this->entityManager->flush();
        return $day->getEmployee()->getId();
    }
}