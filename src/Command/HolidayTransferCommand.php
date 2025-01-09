<?php

namespace App\Command;

use App\Repository\EmployeeRepository;
use App\Repository\WorktimeSpecialDayRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HolidayTransferCommand extends Command
{

    protected static $defaultName = 'worktime:holidays:transfer';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EmployeeRepository $employeeRepository,
        private readonly WorktimeSpecialDayRepository $worktimeSpecialDayRepository
    ){
        parent::__construct(self::$defaultName);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        date_default_timezone_set('Europe/Berlin');
        ini_set('date.timezone', 'Europe/Berlin');
        $employees = $this->employeeRepository->findAll();
        $currentYear = date('Y');
        $maxDate = new \DateTime($currentYear . '-01-01 00:00:00');
        $minDate = (clone $maxDate)->sub(new DateInterval('P1Y'));
        foreach ($employees as $employee) {
            $holidays = $this->worktimeSpecialDayRepository->findBy(['employee' => $employee, 'date' => [
                'gte' => $minDate->format('Y-m-d'),
                'lte' => $maxDate->format('Y-m-d')
            ]]);
            $leftOverHolidays = $employee->getHolidays() + $employee->getHolidayTransfers() ?? 0 - count($holidays);
            $employee->setHolidayTransfers($leftOverHolidays);
            $this->entityManager->persist($employee);
        }
        $this->entityManager->flush();

        return self::SUCCESS;
    }

}