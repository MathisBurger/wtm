<?php

namespace App\Utility;

use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use DateInterval;
use DateTime;

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

    /**
     * Gets all periods until today
     *
     * @param DateTime $start The start date
     * @return array All periods
     */
    public static function getAllPeriodsFromDateToNow(DateTime $start): array
    {
        $startCopy = $start;
        $end = new DateTime();
        $periods = [];
        while ($start->format("Y-m") !== $end->format("Y-m")) {
            $periods[] = $startCopy->format("Y-m");
            $startCopy->add(new DateInterval('P1M'));
        }
        $periods[] = $end->format("Y-m");
        return $periods;
    }

}