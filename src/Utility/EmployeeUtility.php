<?php

namespace App\Utility;

use App\Entity\ConfiguredWorktime;
use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use App\Entity\WorktimeSpecialDay;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Handling utility actions
 */
class EmployeeUtility
{

    /**
     * Gets the available periods from employee
     *
     * @param Employee $employee The employee
     * @return array[] All three types of data
     */
    public static function getTimePeriodsWithData(Employee $employee): array
    {
        $workTimePeriods = [];
        /** @var WorktimePeriod $period */
        foreach ($employee->getPeriods()->toArray() as $period) {
            if (!in_array($period->getStartTime()->format('Y-m'), $workTimePeriods)) {
                $workTimePeriods[] = $period->getStartTime()->format('Y-m');
            }
        }

        $holidayPeriods = [];
        $illnessPeriods = [];
        /** @var WorktimeSpecialDay $specialDay */
        foreach ($employee->getWorktimeSpecialDays()->toArray() as $specialDay) {
            if ($specialDay->getReason() === WorktimeSpecialDay::REASON_HOLIDAY) {
                if (!in_array($specialDay->getDate()->format('Y'), $holidayPeriods)) {
                    $holidayPeriods[] = $specialDay->getDate()->format('Y');
                }
            } else if ($specialDay->getReason() === WorktimeSpecialDay::REASON_ILLNESS) {
                if (!in_array($specialDay->getDate()->format('Y'), $illnessPeriods)) {
                    $illnessPeriods[] = $specialDay->getDate()->format('Y');
                }
            }
        }
        usort($workTimePeriods, fn (string $a, string $b) => $a <=> $b);
        usort($holidayPeriods, fn (string $a, string $b) => $a <=> $b);
        usort($illnessPeriods, fn (string $a, string $b) => $a <=> $b);
        return [array_reverse($workTimePeriods), array_reverse($holidayPeriods), array_reverse($illnessPeriods)];
    }



    /**
     * Gets nessesary employee data
     *
     * @param Employee $employee The employee
     * @param string|null $timePeriod The time period that should be displayed
     * @return array The data array
     */
    public static function getEmployeeData(Employee $employee, ?string $timePeriod = null, ?string $tab = null, float $regularWorktime): array
    {
        $workTimePeriod = (new DateTime())->format("Y-m");
        if ($timePeriod !== null && $tab === null) {
            $workTimePeriod = $timePeriod;
        }
        // general periods
        $periods = $employee->getPeriods()->filter(
            fn (WorktimePeriod $p) => $p->getStartTime()->format("Y-m") === $workTimePeriod
        );

        // Overtime
        $sumWorkedHours = 0;
        /** @var WorktimePeriod $item */
        foreach ($periods->toArray() as $item) {
            if ($item->getEndTime() !== null) {
                $diff = $item->getStartTime()->diff($item->getEndTime());
                $sumWorkedHours += $diff->h + ($diff->i / 60) + ($diff->s / 3600);
            }
        }
        $workingHours = 0;
        if ($employee->isTimeEmployed()) {
            $workingHours = $regularWorktime;
        }
        $overtime = $sumWorkedHours - $workingHours;
        $firstPeriodStartTime = new DateTime();
        if ($periods->first()) {
            $firstPeriodStartTime = $periods->first()->getStartTime();
        }

        $specialDayTimePeriods = [(new DateTime())->format("Y"), (new DateTime())->add(new DateInterval('P1Y'))->format("Y")];
        if (($tab === "holiday" || $tab === "illness") && $timePeriod !== null) {
            $specialDayTimePeriods = [$timePeriod, null];
        }

        // Holidays
        $holidays = $employee->getWorktimeSpecialDays()->filter(
            fn (WorktimeSpecialDay $d) => $d->getReason() === WorktimeSpecialDay::REASON_HOLIDAY && (
                    $d->getDate()->format("Y") === $specialDayTimePeriods[0]
                    || $d->getDate()->format("Y") === $specialDayTimePeriods[1]
                )
        );

        // Illness days
        $illnessDays = $employee->getWorktimeSpecialDays()->filter(
            fn (WorktimeSpecialDay $d) => $d->getReason() === WorktimeSpecialDay::REASON_ILLNESS && $d->getDate()->format("Y") === $specialDayTimePeriods[0]
        );

        $periodsArray = $periods->toArray();
        usort($periodsArray, fn (WorktimePeriod $a, WorktimePeriod $b) => $a->getStartTime()->getTimestamp() <=> $b->getStartTime()->getTimestamp());
        $periodsSorted = new ArrayCollection($periodsArray);

        $holidaysArray = $holidays->toArray();
        usort($holidaysArray, fn (WorktimeSpecialDay $a, WorktimeSpecialDay $b) => $a->getDate()->getTimestamp() <=> $b->getDate()->getTimestamp());
        $holidaysSorted = new ArrayCollection($holidaysArray);

        $illnessArray = $illnessDays->toArray();
        usort($illnessArray, fn (WorktimeSpecialDay $a, WorktimeSpecialDay $b) => $a->getDate()->getTimestamp() <=> $b->getDate()->getTimestamp());
        $illnessSorted = new ArrayCollection($illnessArray);

        return [$periodsSorted, $overtime, $firstPeriodStartTime, $holidaysSorted, $illnessSorted];
    }

    /**
     * Gets regular worktime for specific day
     *
     * @param Employee $employee The employee
     * @param DateTime $dateTime The datetime
     * @return float The worktime for that weekday
     */
    public static function getWorktimeForDay(Employee $employee, DateTime $dateTime): float
    {
        $sum = 0;
        $workTimePeriod = strtoupper($dateTime->format("l"));
        /** @var ConfiguredWorktime $configuredWorktime */
        foreach ($employee->getConfiguredWorktimes()->toArray() as $configuredWorktime) {
            if ($configuredWorktime->getDayName() === $workTimePeriod) {
                $diff = $configuredWorktime->getRegularStartTime()->diff($configuredWorktime->getRegularEndTime());
                $sum += $diff->h + ($diff->i / 60) + ($diff->s / 3600);
            }
        }
        return $sum;
    }

}