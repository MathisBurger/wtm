<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use App\Entity\WorktimeSpecialDay;
use App\Generator\ReportPdf;
use App\Repository\WorktimePeriodRepository;
use App\Repository\WorktimeSpecialDayRepository;
use App\Utility\DateUtility;
use App\Utility\EmployeeUtility;
use App\Utility\PeriodUtility;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class GeneratorService
{

    public function __construct(
        private readonly WorktimePeriodRepository $periodRepository,
        private readonly WorktimeSpecialDayRepository $specialDayRepository,
        private readonly Environment $environment,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ){}

    /**
     * Generates a report
     *
     * @param string $period
     * @throws Exception Invalid period
     */
    public function generateReport(string $period): void
    {
        $destructedPeriod = PeriodUtility::getYearAndMonthFromPeriod($period);
        if (count($destructedPeriod) !== 2) {
            throw new Exception(
                $this->translator->trans('messages.invalidPeriod')
            );
        }
        [$year, $month] = $destructedPeriod;
        [$employees, $stats] = $this->getEmployeesAndStats($year, $month);


        /**
         * @var string $key
         * @var array $value
         */
        foreach ($employees as $k => $value) {
            usort(
                $value,
                fn (array $a, array $b) => $a['dateUnformatted']->getTimeStamp() <=> $b['dateUnformatted']->getTimeStamp()
            );
            $employees[$k] = $value;
        }
        $html = $this->environment->render('generator/generator.html.twig', [
            'employees' => array_keys($employees),
            'periods' => $employees,
            'stats' => $stats
        ]);
        $pdf = new ReportPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->handleCreation($html, $period);
        $this->logger->info('Created report for period ' . $period);
        $pdf->Output('Monatsbericht.pdf', 'I');
    }

    /**
     * Gets the overtime from last update in db to first element
     * NOTE: Overtime entry is not updated in the database
     * @deprecated This method is no longer used for overtime determination. A more lightweight method is now used
     *
     * @param Employee $employee The employee
     * @param DateTimeInterface $firstCurrent The first current
     * @return float The overtime difference
     */
    public function getOvertime(Employee $employee, DateTimeInterface $firstCurrent): float
    {
        $elementsToSum = [];
        if ($employee->getOvertimeLastUpdate() !== null) {
            $elementsToSum = $this->periodRepository->findForUserWithRestriction($employee->getUsername(), $employee->getOvertimeLastUpdate(), $firstCurrent);
        } else {
            $elementsToSum = $this->periodRepository->findForUserWithRestrictionUpperOnly($employee->getUsername(), $firstCurrent);
        }
        $timeSum = 0;
        /** @var WorktimePeriod $element */
        foreach ($elementsToSum as $element) {
            $diff = (new DateTime())->diff($element->getStartTime());
            if ($element->getEndTime() !== null) {
                $diff = $element->getEndTime()->diff($element->getStartTime());
            }
            $timeSum += $diff->h + ($diff->i / 60) + ($diff->s / 3600);
            $timeSum -= EmployeeUtility::sumBreaksToSubtract($element);
        }
        if ($timeSum === 0) {
            return 0;
        }
        if (!$employee->isTimeEmployed()) {
            return 0;
        }
        $periods = PeriodUtility::getAllPeriodsFromDateToNow(DateTime::createFromInterface($firstCurrent));
        return $timeSum - EmployeeUtility::getWorktimeForPeriods($employee, $periods);
    }

    /**
     * Gets the employees and stats
     *
     * @param int $year The year
     * @param int $month The month
     * @return array[] Array with data
     * @throws Exception Date period error
     */
    private function getEmployeesAndStats(int $year, int $month): array
    {
        $entries = $this->periodRepository->findForPeriod($year, $month);
        $employees = [];
        $targetHours = [];
        $stats = [];

        $specialDays = $this->specialDayRepository->findForPeriod($year, $month);
        /** @var WorktimeSpecialDay $specialDay */
        foreach ($specialDays as $specialDay) {
            $notes = $this->translator->trans('specialDay.' . $specialDay->getReason());
            if ($specialDay->getNotes() !== null) {
                $notes = $notes .': ' . $specialDay->getNotes();
            }

            // Init user stats if no existance
            if (!isset($stats[$specialDay->getEmployee()->getUsername()])) {
                $stats[$specialDay->getEmployee()->getUsername()] = ['hoursWorked' => 0, 'illnessDays' => 0, 'holidays' => 0, 'overtime' => 0];
            }

            if ($specialDay->getReason() === WorktimeSpecialDay::REASON_ILLNESS) {
                $stats[$specialDay->getEmployee()->getUsername()]['illnessDays']++;
            } else if ($specialDay->getReason() === WorktimeSpecialDay::REASON_HOLIDAY) {
                $stats[$specialDay->getEmployee()->getUsername()]['holidays']++;
            }

            // Removes minus overtime on worktime special days
            if ($specialDay->getEmployee()->isTimeEmployed()) {
                $stats[$specialDay->getEmployee()->getUsername()]['overtime'] += EmployeeUtility::getWorktimeForDay($specialDay->getEmployee(), $specialDay->getDate());
            }

            $employees[$specialDay->getEmployee()->getUsername()][] = [
                'dateUnformatted' => $specialDay->getDate(),
                'fullName' => $specialDay->getEmployee()->getFirstName() . " " . $specialDay->getEmployee()->getLastName() . ' (' . $specialDay->getEmployee()->getUsername() .')',
                'date' => $specialDay->getDate()->format('d.m.Y'),
                'startTime' => '-',
                'endTime' => '-',
                'notes' => $notes,
                'isOvertimeDecrease' => false
            ];
        }

        $beforeEntry = null;
        /** @var WorktimePeriod $entry */
        foreach ($entries as $entry) {

            // Init user stats if no existance
            if (!isset($stats[$entry->getEmployee()->getUsername()])) {
                $stats[$entry->getEmployee()->getUsername()] = ['hoursWorked' => 0, 'illnessDays' => 0, 'holidays' => 0, 'overtime' => null];
            }

            // Handle general month statistics
            if ($entry->getEndTime() !== null) {
                $diff = $entry->getEndTime()->diff($entry->getStartTime());
                $stats[$entry->getEmployee()->getUsername()]['hoursWorked'] += $diff->h + ($diff->i / 60) + ($diff->s / 3600);
                $stats[$entry->getEmployee()->getUsername()]['hoursWorked'] -= EmployeeUtility::getBreakSumToSubstract($entry, $beforeEntry);
                $beforeEntry = $entry;
            }

            // Add table element
            $employees[$entry->getEmployee()->getUsername()][] = [
                'dateUnformatted' => $entry->getStartTime(),
                'fullName' => $entry->getEmployee()->getFirstName() . " " . $entry->getEmployee()->getLastName() . ' (' . $entry->getEmployee()->getUsername() .')',
                'date' => $entry->getStartTime()->format('d.m.Y'),
                'startTime' => $entry->getStartTime()->format('H:i'),
                'endTime' => $entry->getEndTime() !== null ? $entry->getEndTime()->format('H:i') : '-',
                'notes' => '',
                'isOvertimeDecrease' => $entry->isOvertimeDecrease()
            ];
            $targetHours[$entry->getEmployee()->getUsername()] = $entry->getEmployee();
        }

        /**
         * @var string $key
         * @var  Employee $value
         */
        foreach ($targetHours as $key => $value) {
            if ($value->getTargetWorkingHours() && $value->isTimeEmployed()) {
                $period = $year . "-" . ($month < 10 ? "0" . $month : $month);
                $targetMonth = EmployeeUtility::getWorktimeForPeriods($value, [$period]);
                $overtime = $stats[$key]['hoursWorked'] - $targetMonth + $stats[$key]['overtime'];
                $stats[$key]['overtime'] = number_format($overtime, 2);
                $transfers = $value->getOvertimeTransfers();
                $newUpdatedAt = DateUtility::getOvertimeLastDayPeriod($year, $month);
                $before = DateUtility::getLastDayOfBeforeMonth($newUpdatedAt);
                $beforeOvertime = (isset($transfers[$before->format('Y-m')]) ? $transfers[$before->format('Y-m')] : 0);
                if (!isset($transfers[$newUpdatedAt->format('Y-m')])) {

                    $transfers[$newUpdatedAt->format('Y-m')] = $overtime + $beforeOvertime;
                }

                $value->setOvertimeTransfers($transfers);
                $this->entityManager->persist($value);
                $this->entityManager->flush();
                $stats[$key]['overtimeTransfer'] = number_format($beforeOvertime, 2);
                $stats[$key]['overtimeTotal'] = number_format($overtime + $beforeOvertime, 2);
            }
        }

        foreach ($stats as $k => $_) {
            $stats[$k]['hoursWorked'] = number_format($stats[$k]['hoursWorked'], 2);
        }
        return [$employees, $stats];
    }
}