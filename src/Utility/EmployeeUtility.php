<?php

namespace App\Utility;

use App\Entity\ConfiguredWorktime;
use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use App\Entity\WorktimeSpecialDay;
use App\RestApi\HolidayApiFactory;
use DateInterval;
use DateTime;
use DateTimeInterface;
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
    public static function getEmployeeData(Employee $employee, ?string $timePeriod = null, ?string $tab = null, ?float $regularWorktime = 0): array
    {
        $workTimePeriod = (new DateTime())->format("Y-m");
        if ($timePeriod !== null && $tab === null) {
            $workTimePeriod = $timePeriod;
        }
        // general periods
        $periods = $employee->getPeriods()->filter(
            fn (WorktimePeriod $p) => $p->getStartTime()->format("Y-m") === $workTimePeriod
        );
        $periodsArray = $periods->toArray();
        usort($periodsArray, fn (WorktimePeriod $a, WorktimePeriod $b) => $a->getStartTime()->getTimestamp() <=> $b->getStartTime()->getTimestamp());
        $periods = new ArrayCollection($periodsArray);

        // Overtime
        $sumWorkedHours = 0;
        $lastBreaks = null;
        /** @var WorktimePeriod $item */
        foreach ($periods->toArray() as $item) {
            if ($item->getEndTime() !== null) {
                $diff = $item->getStartTime()->diff($item->getEndTime());
                $sumWorkedHours += $diff->h + ($diff->i / 60) + ($diff->s / 3600);
                $newLastBreaks = EmployeeUtility::getBreaksForPeriod($item->getEmployee(), $item->getStartTime());
                if ($lastBreaks !== null && self::breakArraysDoMatch($lastBreaks, $newLastBreaks)) {
                    $sumWorkedHours -= EmployeeUtility::sumBreaksToSubtract($item);
                }
                $lastBreaks = $newLastBreaks;
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
     * Gets the break sums to substract dependent on before entry
     *
     * @param WorktimePeriod $entry Current entry
     * @param WorktimePeriod|null $beforeEntry Before entry
     * @return float The time to substract
     */
    public static function getBreakSumToSubstract(WorktimePeriod $entry, ?WorktimePeriod $beforeEntry): float
    {
        if (
            $beforeEntry === null
            || !EmployeeUtility::breakArraysDoMatch(
                EmployeeUtility::getBreaksForPeriod($entry->getEmployee(), $entry->getStartTime()),
                EmployeeUtility::getBreaksForPeriod($beforeEntry->getEmployee(), $beforeEntry->getStartTime())
            )
        ) {
            return EmployeeUtility::sumBreaksToSubtract($entry);
        }
        return 0;
    }

    /**
     * Gets regular worktime for specific day
     *
     * @param Employee $employee The employee
     * @param DateTimeInterface $dateTime The datetime
     * @return float The worktime for that weekday
     */
    public static function getWorktimeForDay(Employee $employee, DateTimeInterface $dateTime): float
    {
        $sum = 0;
        $workTimePeriod = strtoupper($dateTime->format("l"));
        /** @var ConfiguredWorktime $configuredWorktime */
        foreach ($employee->getConfiguredWorktimes()->toArray() as $configuredWorktime) {
            if ($configuredWorktime->getDayName() === $workTimePeriod) {
                $diff = $configuredWorktime->getRegularStartTime()->diff($configuredWorktime->getRegularEndTime());
                $sum += $diff->h + ($diff->i / 60) + ($diff->s / 3600);
                if ($configuredWorktime->getBreakDuration() !== null) {
                    $sum -= $configuredWorktime->getBreakDuration();
                }
            }
        }
        return $sum;
    }

    /**
     * Gets the worktime for many periods
     *
     * @param Employee $employee The employee
     * @param array $periods All periods
     * @return float The sum of worktime
     */
    public static function getWorktimeForPeriods(Employee $employee, array $periods): float
    {
        $sum = 0;
        foreach ($periods as $period) {
            [$year, $month] = PeriodUtility::getYearAndMonthFromPeriod($period);
            $sum += self::getWorktimeForPeriod($employee, $year, $month);
        }
        return $sum;
    }

    /**
     * Gets the breaks for a specific day
     *
     * @param Employee $employee The employee
     * @param DateTimeInterface $dateTime The datetime interface
     * @return array All breaks
     */
    public static function getBreaksForPeriod(Employee $employee, DateTimeInterface $dateTime): array
    {
        $breaks = [];
        $workTimePeriod = strtoupper($dateTime->format("l"));
        /** @var ConfiguredWorktime $configuredWorktime */
        foreach ($employee->getConfiguredWorktimes()->toArray() as $configuredWorktime) {
            if ($configuredWorktime->getDayName() === $workTimePeriod) {
                if ($configuredWorktime->getBreakDuration() !== null) {
                    $breaks[] = [$configuredWorktime->getBreakDuration(), $configuredWorktime->getBreakStart(), $configuredWorktime->getId()];
                }
            }
        }
        return $breaks;
    }

    /**
     * Gets the sum of time to subtract for breaks
     *
     * @param WorktimePeriod $element The worktime period
     * @return float The sum of breaks
     */
    public static function sumBreaksToSubtract(WorktimePeriod $element): float
    {
        $timeSum = 0;
        $breaks = EmployeeUtility::getBreaksForPeriod($element->getEmployee(), $element->getStartTime());
        foreach ($breaks as $break) {
            /** @var DateTimeInterface $endTime */
            $endDate = clone $element->getEndTime();
            if ($endDate === null) continue;
            $endTime = DateTime::createFromInterface($endDate)->setDate(1970, 1,1);
            if ($break[1]->getTimestamp() < $endTime->getTimestamp()) {
                $timeSum += $break[0] ?? 0;
            }
        }
        return $timeSum;
    }

    /**
     * Checks if break arrays do match
     *
     * @param array $arr1 First array
     * @param array $arr2 Second array
     * @return bool if they match
     */
    public static function breakArraysDoMatch(array $arr1, array $arr2): bool
    {
        if (count($arr1) !== count($arr2)) {
            return false;
        }
        for ($i = 0; $i < count($arr1); $i++) {
            if ($arr1[$i][2] !== $arr2[$i][2]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Gets the required worktime for period
     *
     * @param Employee $employee The employee
     * @param int $year The year of the period
     * @param int $month The month
     * @return float The worktime
     */
    private static function getWorktimeForPeriod(Employee $employee, int $year, int $month): float
    {
        $startDate = new DateTime();
        $startDate->setDate($year, $month, 1);
        $startDate->setTime(0, 0, 0);
        $endDate = new DateTime();
        $endDate->setDate($year, $month, DateUtility::getMonthMaxDay($year, $month));
        $endDate->setTime(23, 59, 0);
        $daysInPeriod = HolidayApiFactory::create()->getWithoutHolidays($startDate, $endDate);
        $holidays = $employee->getWorktimeSpecialDays()->filter(
            fn (WorktimeSpecialDay $specialDay) => $specialDay->getReason() === WorktimeSpecialDay::REASON_HOLIDAY && $specialDay->getDate()->format("Y-m") === $startDate->format("Y-m")
        );
        $sum = 0;
        /** @var DateTime $day */
        foreach ($daysInPeriod as $day) {
            $sum += EmployeeUtility::getWorktimeForDay($employee, $day);
        }
        /** @var WorktimeSpecialDay $holiday */
        foreach ($holidays as $holiday) {
            $sum -= EmployeeUtility::getWorktimeForDay($employee, $holiday->getDate());
        }
        return $sum;
    }

}