<?php

namespace App\Service;

use App\Entity\WorktimePeriod;
use App\Repository\EmployeeRepository;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
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

    /**
     * Is returned on early login
     */
    public const EARLY_LOGIN = "early_login";

    /**
     * Is returned on early login
    */
    public const EARLY_LOGOUT = "early_logout";

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
        if ($employee->getRestrictedStartTime() !== null && self::compareBefore($employee->getRestrictedStartTime())) {
            return self::EARLY_LOGIN;
        }
        $checkIn = new WorktimePeriod();
        $checkIn->setEmployee($employee);
        $employee->addPeriod($checkIn);
        $checkIn->setStartTime(new DateTime());
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
        if ($employee->getRestrictedEndTime() && self::compareBefore($employee->getRestrictedEndTime())) {
            return self::EARLY_LOGOUT;
        }
        $currentCheckIn->setEndTime(new DateTime());
        $this->entityManager->persist($currentCheckIn);
        $this->entityManager->flush();
        return self::SUCCESS;
    }

    /**
     * Gets the required action the user has to perform
     *
     * @param string $username The username of the user
     * @return string The action / error message
     */
    public function getRequiredAction(string $username): string {
        $employee = $this->employeeRepository->findOneBy(['username' => $username]);
        if ($employee === null) {
            return self::USER_DOES_NOT_EXIST;
        }
        /** @var WorktimePeriod|false $currentCheckIn */
        $currentCheckIn = $employee->getPeriods()->last();
        if ($currentCheckIn === false || $currentCheckIn->getEndTime() !== null) {
            return 'checkIn';
        }
        return 'checkOut';
    }

    /**
     * Compares date before
     *
     * @param DateTimeInterface $date1 Date
     * @return bool Result
     */
    private static function compareBefore(DateTimeInterface $date1): bool
    {
         $now = new DateTime();
         $now->setDate(1970,1,1);
         $timestamp = $now->getTimestamp();
         return $timestamp < $date1->getTimestamp();
    }
}