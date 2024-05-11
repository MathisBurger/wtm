<?php

namespace App\Utility;

/**
 * Utility handling period
 */
class PeriodUtility
{

    /**
     * Gets year and month from period
     *
     * @param string $period The period
     * @return array The array of year and month
     */
    public static function getYearAndMonthFromPeriod(string $period): array
    {
        $spl = explode("-", $period);
        if (count($spl) !== 2) {
            return [];
        }
        $year = intval($spl[0]);
        $month = intval($spl[1]);
        if ($year === 0 || $month === 0) {
            return [];
        }
        return [$year, $month];
    }

}