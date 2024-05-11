<?php

namespace App\Utility;

use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use App\Entity\WorktimeSpecialDay;
use DateInterval;
use DateTime;

/**
 * Handling utility actions
 */
class EmployeeUtility
{

    /**
     * Gets nessesary employee data
     *
     * @param Employee $employee The employee
     * @return array The data array
     */
    public static function getEmployeeData(Employee $employee): array
    {
        // general periods
        $periods = $employee->getPeriods()->filter(
            fn (WorktimePeriod $p) => $p->getStartTime()->format("Y-m") === (new DateTime())->format("Y-m")
        );

        // Overtime
        $sumWorkedHours = 0;
        /** @var WorktimePeriod $item */
        foreach ($periods->toArray() as $item) {
            if ($item->getEndTime() !== null) {
                $diff = $item->getStartTime()->diff($item->getEndTime());
                $sumWorkedHours += $diff->h + ($diff->i / 60);
            }
        }
        $overtime = $sumWorkedHours - ($employee->getTargetWorkingHours() ?? 0) * 4.34524;
        $firstPeriodStartTime = new DateTime();
        if ($periods->first()) {
            $firstPeriodStartTime = $periods->first()->getStartTime();
        }

        // Holidays
        $holidays = $employee->getWorktimeSpecialDays()->filter(
            fn (WorktimeSpecialDay $d) => $d->getReason() === WorktimeSpecialDay::REASON_HOLIDAY && (
                    $d->getDate()->format("Y") === (new DateTime())->format("Y")
                    || $d->getDate()->format("Y") === (new DateTime())->add(new DateInterval('P1Y'))->format("Y")
                )
        );

        // Illness days
        $illnessDays = $employee->getWorktimeSpecialDays()->filter(
            fn (WorktimeSpecialDay $d) => $d->getReason() === WorktimeSpecialDay::REASON_ILLNESS && $d->getDate()->format("Y") === (new DateTime())->format("Y")
        );

        return [$periods, $overtime, $firstPeriodStartTime, $holidays, $illnessDays];
    }

}