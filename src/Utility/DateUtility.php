<?php

namespace App\Utility;

use DateTime;

/**
 * Utility handling dates
 */
class DateUtility
{

    /**
     * Gets the max day of a month
     *
     * @param int $year The year
     * @param int $month The month
     * @return int The max day
     */
    public static function getMonthMaxDay(int $year, int $month): int
    {
        if ($year % 4 === 0 && $month == 2) return 29;
        if ($month == 2) {
            return 28;
        }
        if (in_array($month, [1,3,5,7,8,10,12])) {
            return 31;
        }
        return 30;
    }

    /**
     * @param int $year The year
     * @param int $month The month
     * @return DateTime The datetime
     * @throws \Exception Date creation exception
     */
    public static function getOvertimeLastDayPeriod(int $year, int $month): DateTime
    {
        return new \DateTime($year . '-' . $month . '-' . DateUtility::getMonthMaxDay($year, $month) . ' 23:59:59');
    }

}