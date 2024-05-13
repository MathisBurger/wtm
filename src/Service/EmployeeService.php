<?php

namespace App\Service;

use App\Entity\ConfiguredWorktime;
use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use App\Exception\EmployeeException;
use App\Repository\EmployeeRepository;
use App\RestApi\HolidayApiFactory;
use App\RestApi\HolidayApiInterface;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Service that handles employee actions
 */
class EmployeeService
{

    private HolidayApiInterface $holidayApi;

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly GeneratorService $generatorService,
        private readonly TranslatorInterface $translator
    ){
        $this->holidayApi = HolidayApiFactory::create();
    }

    /**
     * Creates a new employee
     *
     * @param FormInterface $form The actual form
     * @return Employee|null The new employee
     * @throws Exception
     */
    public function createEmployee(FormInterface $form): ?Employee {
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Employee $employee */
            $employee = $form->getData();
            $employee->setUsername(strtolower($employee->getUsername()));
            $exists = $this->employeeRepository->findOneBy(['username' => $employee->getUsername()]);
            if ($exists) {
                throw new EmployeeException(
                    $this->translator->trans('form.userAlreadyExists')
                );
            }
            return $this->persistEmployee($employee);
        }
        throw new Exception($form->getErrors()[0]->getMessage());
    }

    /**
     * Updates a employee
     *
     * @param FormInterface $form The actual form
     * @return Employee|null The updated employee
     * @throws Exception
     */
    public function updateEmployee(FormInterface $form): ?Employee {
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Employee $employee */
            $employee = $form->getData();
            $employee->setUsername(strtolower($employee->getUsername()));
            $exists = $this->employeeRepository->findOneBy(['username' => $employee->getUsername()]);
            if (!$exists) {
                throw new EmployeeException(
                    $this->translator->trans('messages.userDoesNotExist')
                );
            }
            $exists->setConfiguredWorktimes($employee->getConfiguredWorktimes());
            $exists->setFirstName($employee->getFirstName());
            $exists->setLastName($employee->getLastName());
            $exists->setIsTimeEmployed($employee->isTimeEmployed());
            return $this->persistEmployee($employee);
        }
        throw new Exception($form->getErrors()[0]->getMessage());
    }

    /**
     * Deletes an existing employee from the database
     *
     * @param int $id The ID of the employee
     * @return void
     */
    public function deleteEmployee(int $id): void
    {
        $employee = $this->employeeRepository->find($id);
        if (!$employee) {
            throw new NotFoundHttpException("Employee not found");
        }
        $this->entityManager->remove($employee);
        $this->entityManager->flush();
    }

    /**
     * Registers a new overtime
     *
     * @param int $id The ID of the employee
     * @param FormInterface $form The form
     * @return void
     * @throws Exception On error
     */
    public function registerOvertime(int $id, FormInterface $form): void
    {
        $employee = $this->employeeRepository->find($id);
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new Exception($this->translator->trans('error.invalidForm'));
        }
        $data = $form->getData();
        $totalOvertime = 0;
        if (!isset($data['isMultiDay']) || !$data['isMultiDay']) {
            $totalOvertime += $this->createWorktimeOvertimeDecreasePeriod($employee, $data['startDate']);
        } else {
            $holidays = $this->holidayApi->getWithoutHolidays($data['startDate'], $data['endDate']);
            foreach ($holidays as $nonHoliday) {
                $totalOvertime += $this->createWorktimeOvertimeDecreasePeriod($employee, $nonHoliday);
            }
        }
        $overtimeLeft = $this->getTotalOvertime($employee);
        if ($totalOvertime > $overtimeLeft) {
            throw new Exception($this->translator->trans('error.notEnoughOvertime'));
        }
        $this->entityManager->flush();
    }

    /**
     * Persists the employee
     *
     * @param Employee $employee The employee
     * @return Employee The updated employee
     */
    private function persistEmployee(Employee $employee): Employee
    {
        $this->entityManager->persist($employee);
        /** @var ConfiguredWorktime $worktime */
        foreach ($employee->getConfiguredWorktimes()->toArray() as $worktime) {
            $worktime->setEmployee($employee);
            $this->entityManager->persist($worktime);
        }
        if ($employee->isTimeEmployed()) {
            $employee->setTargetWorkingHours(self::sumUpWeeklyWorktime($employee));
        } else {
            $employee->setTargetWorkingHours(null);
        }

        $this->entityManager->persist($employee);
        $this->entityManager->flush();
        return $employee;
    }

    /**
     * Gets the total overtime
     *
     * @param Employee $employee The employee
     * @return float The total overtime
     */
    private function getTotalOvertime(Employee $employee): float
    {
        if (!$employee->isTimeEmployed()) {
            return 0;
        }
        $firstCurrentArray = $employee->getPeriods()->toArray();
        usort($firstCurrentArray, fn (WorktimePeriod $a, WorktimePeriod $b) => $a->getStartTime()->getTimestamp() <=> $b->getStartTime()->getTimestamp());
        // Caution: getOvertime() does not update the overtime in database
        return $this->generatorService->getOvertime($employee, $firstCurrentArray[count($firstCurrentArray)-1]);
    }

    /**
     * Creates a new worktime overtime decrease period
     * NOTE: The data is not persisted into database here
     *
     * @param Employee $employee The employee
     * @param DateTimeInterface $dateTime The date
     * @return int The amount of overtime at this date
     */
    private function createWorktimeOvertimeDecreasePeriod(Employee $employee, DateTimeInterface $dateTime): int
    {
        $overtimeOnDay = 0;
        $upperWeekDay = strtoupper($dateTime->format('l'));
        $configuredTimes = $employee->getConfiguredWorktimes()->filter(fn (ConfiguredWorktime $w) => $w->getDayName() === $upperWeekDay);
        /** @var ConfiguredWorktime $time */
        foreach ($configuredTimes->toArray() as $time) {
            $diff = $time->getRegularStartTime()->diff($time->getRegularEndTime());
            $overtimeOnDay += $diff->h + ($diff->i / 60);
        }
        $startTime = DateTime::createFromInterface($dateTime);
        $startTime->setTime(1,0,0);
        $period = new WorktimePeriod();
        $period->setOvertimeDecrease(true);
        $period->setEmployee($employee);
        $period->setStartTime($startTime);
        $period->setEndTime($startTime);
        $this->entityManager->persist($period);
        $employee->addPeriod($period);
        $this->entityManager->persist($employee);
        return $overtimeOnDay;
    }

    /**
     * Sums up the weekly worktime of an employee
     *
     * @param Employee $employee The employee
     * @return float The sum
     */
    private static function sumUpWeeklyWorktime(Employee $employee): float
    {
        $sum = 0;
        /** @var ConfiguredWorktime $worktime */
        foreach ($employee->getConfiguredWorktimes()->toArray() as $worktime) {
            $interval = $worktime->getRegularEndTime()->diff($worktime->getRegularStartTime());
            $sum += $interval->h + ($interval->i / 60);
        }
        return $sum;
    }
}