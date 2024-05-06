<?php

namespace App\Command;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The pre init command for first data collection to add all users
 */
#[AsCommand(name: 'worktime:employee:add')]
class PreinitEmployeeCommand extends Command
{
    protected static $defaultName = 'worktime:employee:add';

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = strtolower($io->ask("Username"));
        $firstName = $io->ask("First name");
        $lastName = $io->ask("Last name");
        $exists = $this->employeeRepository->findOneBy(['username' => $username]) !== null;
        if ($exists) {
            $io->error("Username already exists");
            return self::SUCCESS;
        }
        $employee = new Employee();
        $employee->setUsername($username);
        $employee->setFirstName($firstName);
        $employee->setLastName($lastName);
        $employee->setTargetWorkingHours(0.0);
        $employee->setTargetWorkingPresent(false);
        $employee->setTargetWorkingTimeBegin(null);
        $employee->setTargetWorkingTimeEnd(null);
        $this->entityManager->persist($employee);
        $this->entityManager->flush();
        $io->success("Employee added successfully");
        return self::SUCCESS;
    }
}