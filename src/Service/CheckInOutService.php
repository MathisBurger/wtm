<?php

namespace App\Service;

use App\Entity\ConfiguredWorktime;
use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use App\Repository\EmployeeRepository;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        private readonly EmployeeRepository $employeeRepository,
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Checks in a user
     *
     * @param string $username The username
     * @param string|null $loginDevice The login device
     * @return string The result string
     */
    public function checkIn(string $username, ?string $loginDevice): string {
        $employee = $this->employeeRepository->findOneBy(['username' => $username]);
        if ($employee === null) {
            return self::USER_DOES_NOT_EXIST;
        }
        /** @var WorktimePeriod|false $currentCheckIn */
        $currentCheckIn = $employee->getPeriods()->last();
        if ($currentCheckIn !== false && $currentCheckIn->getEndTime() === null) {
            return self::ALREADY_CHECKED_IN;
        }
        if ($employee->isTimeEmployed() && !self::canCheckIn($employee)) {
            return self::EARLY_LOGIN;
        }
        $checkIn = new WorktimePeriod();
        $checkIn->setEmployee($employee);
        $employee->addPeriod($checkIn);
        $checkIn->setStartTime(new DateTime());
        $checkIn->setLoginDevice($loginDevice);
        $this->entityManager->persist($checkIn);
        $this->entityManager->persist($employee);
        $this->entityManager->flush();
        return self::SUCCESS;
    }

    /**
     * Checks out the current user
     *
     * @param string $username The username of the user
     * @param string|null $logoutDevice The logout device
     * @return string The checkout string
     */
    public function checkOut(string $username, ?string $logoutDevice): string
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
        if ($employee->isTimeEmployed() && !self::canCheckOut($employee)) {
            return self::EARLY_LOGOUT;
        }
        $currentCheckIn->setEndTime(new DateTime());
        $currentCheckIn->setLogoutDevice($logoutDevice);
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
            return $this->translator->trans('messages.unknownUserDesktop');
        }
        /** @var WorktimePeriod|false $currentCheckIn */
        $currentCheckIn = $employee->getPeriods()->last();
        if ($currentCheckIn === false || $currentCheckIn->getEndTime() !== null) {
            return 'checkIn';
        }
        return 'checkOut';
    }

    /**
     * Checks if a user can check in
     *
     * @param Employee $employee The employee
     * @return bool If can check in
     */
    private static function canCheckIn(Employee $employee): bool
    {
        $time = new DateTime();
        $today = strtoupper($time->format('l'));
        $time->setDate(1970,1,1);
        $configured = $employee->getConfiguredWorktimes()->filter(
            fn (ConfiguredWorktime $c) => $c->getDayName() === $today
        );
        if ($configured->count() === 0) {
            return true;
        }
        $lowestDiff = PHP_INT_MAX;
        /** @var ConfiguredWorktime $lowest */
        $lowest = null;

        /** @var ConfiguredWorktime $config */
        foreach ($configured->toArray() as $config) {
            $diff = $time->getTimestamp() - $config->getRegularStartTime()->getTimestamp();
            if ($diff > 0) continue;
            if ($diff < $lowestDiff) {
                $lowestDiff = $diff;
                $lowest = $config;
            }
        }
        if ($lowest && $lowest->getRestrictedStartTime()) {
            return !self::compareBefore($lowest->getRestrictedStartTime());
        }
        return true;
    }

    /**
     * Checks if a user can check out
     *
     * @param Employee $employee The employee
     * @return bool If can check out
     */
    private static function canCheckOut(Employee $employee): bool
    {
        $time = new DateTime();
        $today = strtoupper($time->format('l'));
        $time->setDate(1970,1,1);
        $configured = $employee->getConfiguredWorktimes()->filter(
            fn (ConfiguredWorktime $c) => $c->getDayName() === $today
        );
        if ($configured->count() === 0) {
            return true;
        }
        $lowestDiff = PHP_INT_MAX;
        /** @var ConfiguredWorktime $lowest */
        $lowest = $configured->first();

        /** @var ConfiguredWorktime $config */
        foreach ($configured->toArray() as $config) {
            $diff = $time->getTimestamp() - $config->getRegularEndTime()->getTimestamp();
            if ($diff > 0) continue;
            if ($diff < $lowestDiff) {
                $lowestDiff = $diff;
                $lowest = $config;
            }
        }
        if ($lowest->getRestrictedEndTime()) {
            return !self::compareBefore($lowest->getRestrictedEndTime());
        }
        return true;
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