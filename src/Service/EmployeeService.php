<?php

namespace App\Service;

use App\Entity\ConfiguredWorktime;
use App\Entity\Employee;
use App\Exception\EmployeeException;
use App\Repository\EmployeeRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Service that handles employee actions
 */
class EmployeeService
{

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly EntityManagerInterface $entityManager
    ){}

    /**
     * Creates a new employee
     *
     * @param FormInterface $form The actual form
     * @return Employee|null The new employee
     * @throws Exception
     */
    public function createEmployee(FormInterface $form): ?Employee {
        try {
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var Employee $employee */
                $employee = $form->getData();
                $employee->setUsername(strtolower($employee->getUsername()));
                $exists = $this->employeeRepository->findOneBy(['username' => $employee->getUsername()]);
                if ($exists) {
                    throw new EmployeeException("Dieser Mitarbeiter existiert bereits");
                }

                $this->entityManager->persist($employee);
                /** @var ConfiguredWorktime $worktime */
                foreach ($employee->getConfiguredWorktimes()->toArray() as $worktime) {
                    $worktime->setEmployee($employee);
                    $this->entityManager->persist($worktime);
                }
                $employee->setTargetWorkingHours(self::sumUpWeeklyWorktime($employee));
                $this->entityManager->persist($employee);
                $this->entityManager->flush();
                return $employee;
            }
            throw new Exception($form->getErrors()[0]->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Updates a employee
     *
     * @param FormInterface $form The actual form
     * @return Employee|null The updated employee
     * @throws Exception
     */
    public function updateEmployee(FormInterface $form): ?Employee {
        try {
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var Employee $employee */
                $employee = $form->getData();
                $employee->setUsername(strtolower($employee->getUsername()));
                $exists = $this->employeeRepository->findOneBy(['username' => $employee->getUsername()]);
                if (!$exists) {
                    throw new EmployeeException("Dieser Mitarbeiter existiert nicht");
                }
                $exists->setConfiguredWorktimes($employee->getConfiguredWorktimes());
                $exists->setUsername($employee->getUsername());
                $exists->setFirstName($employee->getFirstName());
                $exists->setLastName($employee->getLastName());
                /** @var ConfiguredWorktime $worktime */
                foreach ($exists->getConfiguredWorktimes()->toArray() as $worktime) {
                    $worktime->setEmployee($employee);
                    $this->entityManager->persist($worktime);
                }
                $exists->setTargetWorkingHours(self::sumUpWeeklyWorktime($exists));
                $this->entityManager->persist($exists);
                $this->entityManager->flush();
                return $employee;
            }
            throw new Exception($form->getErrors()[0]->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
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