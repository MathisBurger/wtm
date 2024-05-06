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
    #[ORM\Column]
    private ?float $startTime = null;

    /**
     * The end time of worktime period
     */
    #[ORM\Column(nullable: true)]
    private ?float $endTime = null;

    /**
     * The date of the worktime period
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $date = null;

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
    public function getStartTime(): ?float
    {
        return $this->startTime;
    }

    /**
     * Sets the start time
     *
     * @param float $startTime The new start time
     * @return $this The updated entity
     */
    public function setStartTime(float $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Gets the end time
     *
     * @return float|null The end time
     */
    public function getEndTime(): ?float
    {
        return $this->endTime;
    }

    /**
     * Sets the end time
     *
     * @param float $endTime The new end time
     * @return $this The updated entity
     */
    public function setEndTime(float $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Gets the date
     *
     * @return DateTimeInterface|null The date
     */
    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Sets the date
     *
     * @param DateTimeInterface $date The new date
     * @return $this The updated entity
     */
    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

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
