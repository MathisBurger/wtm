<?php

namespace App\Service;

use App\Entity\WorktimeSpecialDay;
use App\Repository\EmployeeRepository;
use App\RestApi\HolidayApiFactory;
use App\RestApi\HolidayApiInterface;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\FormInterface;

class WorktimeSpecialDayService
{

    private HolidayApiInterface $holidayApi;

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly EntityManagerInterface $entityManager
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
    public function createSpecialDays(int $id, FormInterface $form)
    {
        $employee = $this->employeeRepository->find($id);
        if (!$employee) {
            throw new Exception("Der Mitarbeiter existiert nicht.");
        }
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new Exception("Das Formular ist ungültig");
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
        }
        if (
            $formData['reason'] === WorktimeSpecialDay::REASON_HOLIDAY
            && $formData['startDate']->format("Y") === (new DateTime())->format("Y")
            && $employee->getHolidays() - $existingHolidays->count() <= 0
        ) {
            throw new Exception("Nicht mehr ausreichend Urlaubstage verfügbar.");
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



}