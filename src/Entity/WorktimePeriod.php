<?php

namespace App\Entity;

use App\Repository\WorktimePeriodRepository;
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
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $endTime = null;

    /**
     * The employee that is refered to this period
     */
    #[ORM\ManyToOne(targetEntity: Employee::class, inversedBy: 'periods')]
    private ?Employee $employee = null;

    /**
     * Gets the start time of period
     *
     * @return float|null The start time
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
}
