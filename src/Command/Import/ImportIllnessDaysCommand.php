<?php

namespace App\Command\Import;

use App\Entity\Employee;
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
 * Imports illness days
 */
class ImportIllnessDaysCommand extends Command
{

    protected static $defaultName = 'worktime:import:illness';

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
            if (count($row) === 5 && $row[0] !== "Mitarbeiter Name") {
                $nameSpl = explode(" ", $row[0]);
                $employee = $this->employeeRepository->findOneBy(['firstName' => $nameSpl[0], 'lastName' => $nameSpl[1]]);
                if ($employee === null) continue;
                $from = DateTime::createFromFormat("d-m-Y", $row[2]);
                $to = DateTime::createFromFormat("d-m-Y", $row[3]);
                if ($from->format("Y-m-d") === $to->format("Y-m-d")) {
                    $this->addDay($from, $employee);
                    continue;
                }
                while ($from->format("Y-m-d") !== $to->format("Y-m-d")) {
                    $this->addDay($from, $employee);
                    $from->add(new DateInterval('P1D'));
                }
                $this->addDay($to, $employee);
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
    private function addDay(DateTime $date, Employee $employee)
    {
        $period = new WorktimeSpecialDay();
        $period->setReason(WorktimeSpecialDay::REASON_ILLNESS);
        $period->setDate($date);
        $period->setEmployee($employee);
        $this->entityManager->persist($period);
        $employee->addWorktimeSpecialDay($period);
        $this->entityManager->persist($employee);
        $this->entityManager->flush();
    }
}