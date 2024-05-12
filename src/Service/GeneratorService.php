<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use App\Entity\WorktimeSpecialDay;
use App\Generator\ReportPdf;
use App\Repository\WorktimePeriodRepository;
use App\Repository\WorktimeSpecialDayRepository;
use App\Utility\PeriodUtility;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;
use TCPDF;
use Twig\Environment;

class GeneratorService
{

    public function __construct(
        private readonly WorktimePeriodRepository $periodRepository,
        private readonly WorktimeSpecialDayRepository $specialDayRepository,
        private readonly Environment $environment,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager
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
        $pdf->Output('Monatsbericht.pdf', 'I');
    }

    /**
     * Gets the overtime from last update in db to first element
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
        foreach ($elementsToSum as $element) {
            $diff = $element->getEndTime()->diff($element->getStartTime());
            $timeSum += $diff->h + ($diff->i / 60) + ($diff->s / 3600);
        }
        if ($timeSum === 0) {
            return 0;
        }
        return $timeSum - ($employee->getTargetWorkingHours() ?? 0) * 4.34524;
    }

    /**
     * Gets the employees and stats
     *
     * @param int $year The year
     * @param int $month The month
     * @return array[] Array with data
     */
    private function getEmployeesAndStats(int $year, int $month): array
    {
        $entries = $this->periodRepository->findForPeriod($year, $month);
        $employees = [];
        $targetHours = [];
        $stats = [];

        /** @var WorktimePeriod $entry */
        foreach ($entries as $entry) {

            // Init user stats if no existance
            if (!isset($stats[$entry->getEmployee()->getUsername()])) {
                $stats[$entry->getEmployee()->getUsername()] = ['hoursWorked' => 0, 'illnessDays' => 0, 'holidays' => 0, 'overtime' => null];
            }

            // Handle general month statistics
            if ($entry->getEndTime() !== null) {
                $diff = $entry->getEndTime()->diff($entry->getStartTime());



                $stats[$entry->getEmployee()->getUsername()]['hoursWorked'] += $diff->h + ($diff->i / 60);
            }

            // Add table element
            $employees[$entry->getEmployee()->getUsername()][] = [
                'dateUnformatted' => $entry->getStartTime(),
                'fullName' => $entry->getEmployee()->getFirstName() . " " . $entry->getEmployee()->getLastName() . ' (' . $entry->getEmployee()->getUsername() .')',
                'date' => $entry->getStartTime()->format('d.m.Y'),
                'startTime' => $entry->getStartTime()->format('H:i'),
                'endTime' => $entry->getEndTime() !== null ? $entry->getEndTime()->format('H:i') : '-',
                'notes' => ''
            ];
            $targetHours[$entry->getEmployee()->getUsername()] = $entry->getEmployee();
        }

        /**
         * @var string $key
         * @var  Employee $value
         */
        foreach ($targetHours as $key => $value) {
            if ($value->getTargetWorkingHours() && $value->isTimeEmployed()) {
                $targetMonth = $value->getTargetWorkingHours() * 4.34524;
                $overtime = $stats[$key]['hoursWorked'] - $targetMonth;
                $stats[$key]['overtime'] = $overtime;
                $overtimeTransferDiff = $this->getOvertime($value, $employees[$key][0]['dateUnformatted']);
                if ($overtimeTransferDiff != 0) {
                    $value->setOvertime($value->getOvertime() + $overtimeTransferDiff);
                    $value->setOvertimeLastUpdate(new DateTime());
                    $this->entityManager->persist($value);
                    $this->entityManager->flush();
                }
                $stats[$key]['overtimeTransfer'] = $value->getOvertime();
                $stats[$key]['overtimeTotal'] = $overtime + $value->getOvertime();
            }
        }

        $specialDays = $this->specialDayRepository->findForPeriod($year, $month);
        /** @var WorktimeSpecialDay $specialDay */
        foreach ($specialDays as $specialDay) {
            $notes = $this->translator->trans('specialDay.' . $specialDay->getReason());
            if ($specialDay->getNotes() !== null) {
                $notes = $notes .': ' . $specialDay->getNotes();
            }

            // Init user stats if no existance
            if (!isset($stats[$specialDay->getEmployee()->getUsername()])) {
                $stats[$specialDay->getEmployee()->getUsername()] = ['hoursWorked' => 0, 'illnessDays' => 0, 'holidays' => 0, 'overtime' => null];
            }

            if ($specialDay->getReason() === WorktimeSpecialDay::REASON_ILLNESS) {
                $stats[$specialDay->getEmployee()->getUsername()]['illnessDays']++;
            } else if ($specialDay->getReason() === WorktimeSpecialDay::REASON_HOLIDAY) {
                $stats[$specialDay->getEmployee()->getUsername()]['holidays']++;
            }

            $employees[$specialDay->getEmployee()->getUsername()][] = [
                'dateUnformatted' => $specialDay->getDate(),
                'fullName' => $specialDay->getEmployee()->getFirstName() . " " . $specialDay->getEmployee()->getLastName() . ' (' . $specialDay->getEmployee()->getUsername() .')',
                'date' => $specialDay->getDate()->format('d.m.Y'),
                'startTime' => '-',
                'endTime' => '-',
                'notes' => $notes
            ];
        }
        return [$employees, $stats];
    }
}