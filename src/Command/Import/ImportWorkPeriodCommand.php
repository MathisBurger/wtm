<?php

namespace App\Command\Import;

use App\Entity\WorktimePeriod;
use App\Repository\EmployeeRepository;
use App\Repository\WorktimePeriodRepository;
use Aspera\Spreadsheet\XLSX\Reader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Imports work period
 */
class ImportWorkPeriodCommand extends Command
{

    protected static $defaultName = 'worktime:import:periods';

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

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $reader = new Reader();
        $fileLoc = $io->ask("Please provide the path to your xlsx file");
        $reader->open($fileLoc);

        foreach ($reader as $row) {
            if (count($row) === 9 && $row[0] !== "Name") {
                $nameSpl = explode(" ", $row[0]);
                $employee = $this->employeeRepository->findOneBy(['firstName' => $nameSpl[0], 'lastName' => $nameSpl[1]]);
                if ($employee === null) continue;
                if (str_contains($row[1], "Buchung fehlt") || str_contains($row[2], "Buchung fehlt") || str_contains($row[3], "Feiertag") || str_contains($row[3], "Frei")) continue;
                $period = new WorktimePeriod();
                $period->setEmployee($employee);
                $period->setStartTime(DateTime::createFromFormat('d-m-Y H:i', $row[1]));
                $period->setEndTime(DateTime::createFromFormat('d-m-Y H:i', $row[2]));
                $this->entityManager->persist($period);
                $employee->addPeriod($period);
                $this->entityManager->persist($employee);
                $this->entityManager->flush();
            }
        }
        $io->success("Successfully imported");
        return self::SUCCESS;
    }
}