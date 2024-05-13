<?php

namespace App\Command\Import;

use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use App\Entity\WorktimeSpecialDay;
use App\Repository\EmployeeRepository;
use Aspera\Spreadsheet\XLSX\Reader;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Import of holidays
 */
class ImportHolidaysCommand extends Command
{

    protected static $defaultName = 'worktime:import:holidays';

    public function __construct(
        private readonly EmployeeRepository $employeeRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $reader = new Reader();
        $fileLoc = $io->ask("Please provide the path to your xlsx file");
        $reader->open($fileLoc);
        foreach ($reader as $row) {
            if (count($row) === 8 && $row[0] !== "Name") {
                $nameSpl = explode(" ", $row[0]);
                $employee = $this->employeeRepository->findOneBy(['firstName' => $nameSpl[0], 'lastName' => $nameSpl[1]]);
                if ($employee === null) continue;
                $from = DateTime::createFromFormat("d-m-Y", $row[2]);
                $to = DateTime::createFromFormat("d-m-Y", $row[3]);
                if ($from->format("Y-m-d") === $to->format("Y-m-d")) {
                    $this->addDay($from, $employee, $row[5]);
                    continue;
                }
                while ($from->format("Y-m-d") !== $to->format("Y-m-d")) {
                    $this->addDay($from, $employee, $row[5]);
                    $from->add(new DateInterval('P1D'));
                }
                $this->addDay($to, $employee, $row[5]);
            }
        }
        $io->success("Successfully imported");
        return self::SUCCESS;
    }


    /**
     * Adds a day to employee
     *
     * @param DateTime $date The date
     * @param Employee $employee The employee
     * @return void
     */
    private function addDay(DateTime $date, Employee $employee, string $type)
    {
        if ($type === "Normal") {
            $period = new WorktimeSpecialDay();
            $period->setReason(WorktimeSpecialDay::REASON_HOLIDAY);
            $period->setDate($date);
            $period->setEmployee($employee);
            $this->entityManager->persist($period);
            $employee->addWorktimeSpecialDay($period);
        } else {
            $period = new WorktimePeriod();
            $period->setOvertimeDecrease(true);
            $date->setTime(8,0,0);
            $period->setStartTime($date);
            $period->setEndTime($date);
            $period->setEmployee($employee);
            $this->entityManager->persist($period);
            $employee->addPeriod($period);
        }
        $this->entityManager->persist($employee);
        $this->entityManager->flush();

    }
}