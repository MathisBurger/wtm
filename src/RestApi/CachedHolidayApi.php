<?php

namespace App\RestApi;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * Cached holiday API
 */
class CachedHolidayApi implements HolidayApiInterface
{
    private readonly AdapterInterface $cache;

    private array $holidays;

    public function __construct() {
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function getWithoutHolidays(DateTimeInterface $from, DateTimeInterface $to): array
    {
        $from->setTime(0, 0, 0);
        $to->setTime(0, 0, 0);
        $days = [];
        if ($from->getTimestamp() > $to->getTimestamp()) {
            return [];
        }
        $current = DateTime::createFromInterface($from);
        while ($current->getTimestamp() <= $to->getTimestamp()) {
            if (!$this->isHoliday($current)) {
                $days[] = DateTimeImmutable::createFromMutable($current);
            }
            $current->add(new DateInterval('P1D'));
        }
        return $days;
    }

    /**
     * Checks if a day is a holiday
     *
     * @param DateTimeInterface $date The date of the day
     * @return bool Whether it is a holiday or not
     * @throws InvalidArgumentException Exception
     */
    private function isHoliday(DateTimeInterface $date): bool
    {
        $yearString = $date->format('Y');
        if (!isset($this->holidays[$yearString])) {
            $holidaysOfYear = $this->getHolidays($yearString);
            $this->holidays[$yearString] = [];
            foreach ($holidaysOfYear as $k => $holiday) {
                $this->holidays[$yearString][$holiday['datum']] = $k;
            }
            return $this->isHoliday($date);
        }
        $weekDay = intval($date->format('N'));
        if ($weekDay > 5) {
            return true;
        }
        return isset($this->holidays[$yearString][$date->format('Y-m-d')]);
    }

    /**
     * Gets all holidays in range
     *
     * @param int $year The year
     * @return array All holidays
     * @throws InvalidArgumentException
     */
    private function getHolidays(string $year): array
    {
        $result = $this->cache->getItem('holidays_api_result_'.$year);
        if ($result->isHit()) {
            return $result->get();
        }
        $fetch = $this->fetchHolidays($year);
        $result->set($fetch);
        $this->cache->save($result);
        return $fetch;
    }

    /**
     * Fetches all holidays from API
     *
     * @param int $year The year
     * @return array The json result
     */
    private function fetchHolidays(string $year): array
    {
        $url = 'https://feiertage-api.de/api/?jahr='.$year.'&nur_land=SH';
        $raw = file_get_contents($url);
        return json_decode($raw, true);

    }
}