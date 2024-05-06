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
                if (
                    $employee->isTargetWorkingPresent()
                    && ($employee->getTargetWorkingHours() === null
                    || $employee->getTargetWorkingTimeBegin() === null
                    || $employee->getTargetWorkingTimeEnd() === null)
                ) {
                    $this->entityManager->persist($employee);
                    $this->entityManager->flush();
                    return $employee;
                }
                throw new EmployeeException("Data missing");
            }
            return null;
        } catch (Exception $e) {
            throw $e;
        }
    }
}