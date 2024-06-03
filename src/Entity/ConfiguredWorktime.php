<?php

namespace App\Entity;

use App\Repository\ConfiguredWorktimeRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity containing configured work times
 */
#[ORM\Entity(repositoryClass: ConfiguredWorktimeRepository::class)]
class ConfiguredWorktime extends AbstractEntity
{
    public const MONDAY = 'MONDAY';
    public const TUESDAY = 'TUESDAY';
    public const WEDNESDAY = 'WEDNESDAY';
    public const THURSDAY = 'THURSDAY';
    public const FRIDAY = 'FRIDAY';
    public const SATURDAY = 'SATURDAY';
    public const SUNDAY = 'SUNDAY';

    /**
     * name of the day
     */
    #[ORM\Column(length: 255)]
    private ?string $dayName = null;

    /**
     * Regular start time
     */
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?DateTimeInterface $regularStartTime = null;

    /**
     * Regular start time
     */
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?DateTimeInterface $regularEndTime = null;

    /**
     * Restricted start time
     */
    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $restrictedStartTime = null;

    /**
     * Restricted end time
     */
    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $restrictedEndTime = null;

    /**
     * The employee assigned to a configured work time
     */
    #[ORM\ManyToOne(targetEntity: Employee::class, inversedBy: 'configuredWorktimes')]
    private ?Employee $employee = null;

    /**
     * Break time
     */
    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $breakStart = null;

    /**
     * The duration of the break
     */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $breakDuration = null;

    /**
     * Gets the name of the day
     *
     * @return string|null The name of the day
     */
    public function getDayName(): ?string
    {
        return $this->dayName;
    }

    /**
     * Sets the name of the day
     *
     * @param string $dayName The name of the day
     * @return $this The updated entity
     */
    public function setDayName(string $dayName): self
    {
        $this->dayName = $dayName;

        return $this;
    }

    /**
     * Gets the regular start time
     *
     * @return DateTimeInterface|null regular start time
     */
    public function getRegularStartTime(): ?DateTimeInterface
    {
        return $this->regularStartTime;
    }

    /**
     * Sets the regular start time
     *
     * @param DateTimeInterface $regularStartTime The new start time
     * @return $this The updated entity
     */
    public function setRegularStartTime(DateTimeInterface $regularStartTime): self
    {
        $this->regularStartTime = $regularStartTime;

        return $this;
    }

    /**
     * Gets the regular end time
     *
     * @return DateTimeInterface|null end time
     */
    public function getRegularEndTime(): ?DateTimeInterface
    {
        return $this->regularEndTime;
    }

    /**
     * Sets the regular end time
     *
     * @param DateTimeInterface $regularEndTime The end time
     * @return $this The updated entity
     */
    public function setRegularEndTime(DateTimeInterface $regularEndTime): self
    {
        $this->regularEndTime = $regularEndTime;

        return $this;
    }

    /**
     * Gets the restricted start time
     *
     * @return DateTimeInterface|null restricted start time
     */
    public function getRestrictedStartTime(): ?DateTimeInterface
    {
        return $this->restrictedStartTime;
    }

    /**
     * Sets the restricted start time
     *
     * @param DateTimeInterface|null $restrictedStartTime The restricted start time
     * @return $this The updated entity
     */
    public function setRestrictedStartTime(?DateTimeInterface $restrictedStartTime): static
    {
        $this->restrictedStartTime = $restrictedStartTime;

        return $this;
    }

    /**
     * Gets the restricted end time
     *
     * @return DateTimeInterface|null The end time
     */
    public function getRestrictedEndTime(): ?DateTimeInterface
    {
        return $this->restrictedEndTime;
    }

    /**
     * Sets the restricted end time
     *
     * @param DateTimeInterface|null $restrictedEndTime
     * @return $this
     */
    public function setRestrictedEndTime(?DateTimeInterface $restrictedEndTime): static
    {
        $this->restrictedEndTime = $restrictedEndTime;

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
     * @param Employee $employee The employee
     * @return $this The updated entity
     */
    public function setEmployee(Employee $employee): self
    {
        $this->employee = $employee;
        return $this;
    }

    /**
     * Gets the break start
     *
     * @return DateTimeInterface|null The break start
     */
    public function getBreakStart(): ?DateTimeInterface
    {
        return $this->breakStart;
    }

    /**
     * Sets the break start
     *
     * @param DateTimeInterface $breakStart The new break start
     * @return $this The updated entity
     */
    public function setBreakStart(?DateTimeInterface $breakStart): self
    {
        $this->breakStart = $breakStart;
        return $this;
    }

    /**
     * Gets the break duration
     *
     * @return float|null The break duration
     */
    public function getBreakDuration(): ?float
    {
        return $this->breakDuration;
    }

    /**
     * Sets the break duration
     *
     * @param float|null $breakDuration The new break duration
     * @return $this The updated entity
     */
    public function setBreakDuration(?float $breakDuration): self
    {
        $this->breakDuration = $breakDuration;
        return $this;
    }
}
