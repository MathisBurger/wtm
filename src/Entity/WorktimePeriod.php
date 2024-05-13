<?php

namespace App\Entity;

use App\Repository\WorktimePeriodRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * WorktimePeriod entity
 */
#[ORM\Entity(repositoryClass: WorktimePeriodRepository::class)]
class WorktimePeriod extends AbstractEntity
{

    /**
     * The start time of worktime period
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $startTime = null;

    /**
     * The end time of worktime period
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $endTime = null;

    /**
     * The login device
     */
    #[ORM\Column(nullable: true)]
    private ?string $loginDevice = null;

    /**
     * The logout device
     */
    #[ORM\Column(nullable: true)]
    private ?string $logoutDevice = null;

    /**
     * The employee that is refered to this period
     */
    #[ORM\ManyToOne(targetEntity: Employee::class, inversedBy: 'periods')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'cascade')]
    private ?Employee $employee = null;

    /**
     * Special notes about period
     */
    #[ORM\Column(nullable: true)]
    private ?bool $isOvertimeDecrease = null;

    /**
     * Gets the start time of period
     *
     * @return DateTimeInterface|null The start time
     */
    public function getStartTime(): ?DateTimeInterface
    {
        return $this->startTime;
    }

    /**
     * Sets the start time
     *
     * @param float $startTime The new start time
     * @return $this The updated entity
     */
    public function setStartTime(DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Gets the end time
     *
     * @return float|null The end time
     */
    public function getEndTime(): ?DateTimeInterface
    {
        return $this->endTime;
    }

    /**
     * Sets the end time
     *
     * @param float $endTime The new end time
     * @return $this The updated entity
     */
    public function setEndTime(DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Gets the employee
     *
     * @return Employee|null The employee
     */
    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    /**
     * Sets the employee
     *
     * @param Employee|null $employee The new employee
     * @return $this The updated entity
     */
    public function setEmployee(?Employee $employee): self
    {
       $this->employee = $employee;
       return $this;
    }

    /**
     * Gets the date as string
     *
     * @return string Date as string
     */
    public function getDate(): string
    {
        return $this->startTime->format('d.m.Y');
    }

    /**
     * Gets the start time as string
     *
     * @return string Start time as string
     */
    public function getStartTimeString(): string
    {
        return $this->startTime->format('H:i');
    }

    /**
     * Gets the end time as string
     *
     * @return string End time as string
     */
    public function getEndTimeString(): string
    {
        if ($this->endTime) {
            return $this->endTime->format('H:i');
        }
        return "-";
    }

    public function getTimeDiff(): string
    {
        if ($this->endTime) {
            return $this->startTime->diff($this->endTime)->format('%H:%I');
        }
        return $this->startTime->diff(new DateTime())->format('%H:%I');
    }

    /**
     * Gets the login device
     *
     * @return string|null The device
     */
    public function getLoginDevice(): string
    {
        return $this->loginDevice ?? "-";
    }

    /**
     * Sets the login device
     *
     * @param string|null $device The device
     * @return $this The updated entity
     */
    public function setLoginDevice(?string $device): self
    {
        $this->loginDevice = $device;
        return $this;
    }

    /**
     * Gets the logout device
     *
     * @return string|null The device
     */
    public function getLogoutDevice(): string
    {
        return $this->logoutDevice ?? "-";
    }

    /**
     * Sets the logout device
     *
     * @param string|null $device The device
     * @return $this The updated entity
     */
    public function setLogoutDevice(?string $device): self
    {
        $this->logoutDevice = $device;
        return $this;
    }

    /**
     * Gets wheather overtime decrease
     *
     * @return bool|null Is overtime decrease
     */
    public function isOvertimeDecrease(): ?bool
    {
        return $this->isOvertimeDecrease;
    }

    /**
     * Sets if is overtime decrease
     *
     * @param bool $isDecrease Is decrease
     * @return $this The updated entity
     */
    public function setOvertimeDecrease(bool $isDecrease): self
    {
        $this->isOvertimeDecrease = $isDecrease;
        return $this;
    }
}
