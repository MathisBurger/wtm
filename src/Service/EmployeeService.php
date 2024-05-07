<?php

namespace App\Service;

use App\Entity\Employee;
use App\Exception\EmployeeException;
use App\Repository\EmployeeRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\FormInterface;

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
                if ($employee->isTargetWorkingPresent()) {
                    if (
                        $employee->getTargetWorkingHours() === null
                        || $employee->getTargetWorkingTimeBegin() === null
                        || $employee->getTargetWorkingTimeEnd() === null
                    ) {
                        throw new Exception("Bitte geben sie in diesem Fall alle Werte an");
                    }
                }
                $employee->setUsername(strtolower($employee->getUsername()));
                $exists = $this->employeeRepository->findOneBy(['username' => $employee->getUsername()]);
                if ($exists) {
                    throw new EmployeeException("Dieser Mitarbeiter existiert bereits");
                }
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
                if ($employee->isTargetWorkingPresent()) {
                    if (
                        $employee->getTargetWorkingHours() === null
                        || $employee->getTargetWorkingTimeBegin() === null
                        || $employee->getTargetWorkingTimeEnd() === null
                    ) {
                        throw new Exception("Bitte geben sie in diesem Fall alle Werte an");
                    }
                }
                $employee->setUsername(strtolower($employee->getUsername()));
                $exists = $this->employeeRepository->findOneBy(['username' => $employee->getUsername()]);
                if (!$exists) {
                    throw new EmployeeException("Dieser Mitarbeiter existiert nicht");
                }
                $exists->setUsername($employee->getUsername());
                $exists->setFirstName($employee->getFirstName());
                $exists->setLastName($employee->getLastName());
                $exists->setTargetWorkingPresent($employee->isTargetWorkingPresent());
                $exists->setTargetWorkingHours($employee->getTargetWorkingHours());
                $exists->setTargetWorkingTimeBegin($employee->getTargetWorkingTimeBegin());
                $exists->setTargetWorkingTimeEnd($employee->getTargetWorkingTimeEnd());
                $this->entityManager->persist($exists);
                $this->entityManager->flush();
                return $employee;
            }
            throw new Exception($form->getErrors()[0]->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }
}