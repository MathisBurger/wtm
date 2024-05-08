<?php

namespace App\RestApi;

use DateTimeInterface;

/**
 * Interface for holiday APIs
 */
interface HolidayApiInterface
{

    /**
     * Gets an array of all days that are not holidays
     *
     * @param DateTimeInterface $from Date from
     * @param DateTimeInterface $to Date to
     * @return array All days that are not holidays
     */
    public function getWithoutHolidays(DateTimeInterface $from, DateTimeInterface $to): array;
}