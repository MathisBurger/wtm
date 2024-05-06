<?php

namespace App\Service;

use App\Entity\WorktimePeriod;
use App\Repository\EmployeeRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service handling for check in and check out
 */
class CheckInOutService
{

    /**
     * Returned if user does not exist
     */
    public const USER_DOES_NOT_EXIST = "user_does_not_exist";

    /**
     * Returned if user already checked in
     */
    public const ALREADY_CHECKED_IN = "already_checked_in";

    /**
     * Returned if user already checked in
     */
    public const NOT_CHECKED_IN = "not_checked_in";

    /**
     * Returned if action was successful
     */
    public const SUCCESS = "success";

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EmployeeRepository $employeeRepository
    ){}

    /**
     * Checks in a user
     *
     * @param string $username The username
     * @return string The result string
     */
    public function checkIn(string $username): string {
        $employee = $this->employeeRepository->findOneBy(['username' => $username]);
        if ($employee === null) {
            return self::USER_DOES_NOT_EXIST;
        }
        /** @var WorktimePeriod|false $currentCheckIn */
        $currentCheckIn = $employee->getPeriods()->last();
        if ($currentCheckIn !== false && $currentCheckIn->getEndTime() === null) {
            return self::ALREADY_CHECKED_IN;
        }
        $dateTime = new DateTime();
        $checkIn = new WorktimePeriod();
        $checkIn->setEmployee($employee);
        $employee->addPeriod($checkIn);
        $checkIn->setStartTime(CheckInOutService::getFloatHoursFromDate($dateTime));
        $checkIn->setDate($dateTime);
        $this->entityManager->persist($checkIn);
        $this->entityManager->persist($employee);
        $this->entityManager->flush();
        return self::SUCCESS;
    }

    /**
     * Checks out the current user
     *
     * @param string $username The username of the user
     * @return string The checkout string
     */
    public function checkOut(string $username): string
    {
        $employee = $this->employeeRepository->findOneBy(['username' => $username]);
        if ($employee === null) {
            return self::USER_DOES_NOT_EXIST;
        }
        /** @var WorktimePeriod|false $currentCheckIn */
        $currentCheckIn = $employee->getPeriods()->last();
        if ($currentCheckIn === false || $currentCheckIn->getEndTime() !== null) {
            return self::NOT_CHECKED_IN;
        }
        $currentCheckIn->setStartTime(CheckInOutService::getFloatHoursFromDate(new DateTime()));
        $this->entityManager->persist($currentCheckIn);
        $this->entityManager->flush();
        return self::SUCCESS;
    }


    /**
     * Converts datetime to hour float
     *
     * @param DateTime $dateTime The datetime
     * @return float The hour float
     */
    public static function getFloatHoursFromDate(DateTime $dateTime): float
    {
        $hourString = $dateTime->format('H');
        $minuteString = $dateTime->format('i');
        $hours = floatval($hourString);
        $minutes = floatval($minuteString) / 60 * 100 / 10;
        return $hours + $minutes;
    }

}