<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The employee entity that contains all data
 */
#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME_EMPLOYEE', fields: ['username'])]
class Employee extends AbstractEntity
{


    /**
     * The username of the user
     */
    #[ORM\Column(length: 255)]
    private ?string $username = null;

    /**
     * The firstname of the user
     */
    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    /**
     * The lastname of the user
     */
    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    /**
     * The target working hours
     */
    #[ORM\Column(nullable: true)]
    private ?float $targetWorkingHours = null;

    /**
     * The amount of holidays the user has
     */
    #[ORM\Column(options: ["default" => 0])]
    private ?int $holidays = null;

    /**
     * All working periods of the user
     */
    #[ORM\OneToMany(targetEntity: WorktimePeriod::class, mappedBy: 'employee')]
    private Collection $periods;

    /**
     * All special worktime days
     */
    #[ORM\OneToMany(targetEntity: WorktimeSpecialDay::class, mappedBy: 'employee')]
    private Collection $worktimeSpecialDays;

    /**
     * All configured worktimes
     */
    #[ORM\OneToMany(targetEntity: ConfiguredWorktime::class, mappedBy: 'employee', cascade: ["persist"])]
    private Collection $configuredWorktimes;

    public function __construct() {
        $this->periods = new ArrayCollection();
        $this->worktimeSpecialDays = new ArrayCollection();
        $this->configuredWorktimes = new ArrayCollection();
    }

    /**
     * Gets the username
     *
     * @return string|null The username
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Sets the username
     *
     * @param string $username The new username
     * @return $this The updated entity
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Gets the firstname
     *
     * @return string|null The firstname
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Sets the firstname
     *
     * @param string $firstName The new firstname
     * @return $this The updated entity
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Gets the lastname
     *
     * @return string|null The lastname
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Sets the lastname
     *
     * @param string $lastName The new lastname
     * @return $this The updated entity
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Gets the target working hours
     *
     * @return float|null The target hours
     */
    public function getTargetWorkingHours(): ?float
    {
        return $this->targetWorkingHours;
    }

    /**
     * Sets the target working hours
     *
     * @param float $targetWorkingHours The target hours
     * @return $this The updated entity
     */
    public function setTargetWorkingHours(?float $targetWorkingHours): self
    {
        $this->targetWorkingHours = $targetWorkingHours;

        return $this;
    }

    /**
     * Gets all periods
     *
     * @return Collection<WorktimePeriod> All periods
     */
    public function getPeriods(): Collection
    {
        return $this->periods;
    }

    /**
     * Adds a new period to employee
     *
     * @param WorktimePeriod $period The new period
     * @return $this The updated entity
     */
    public function addPeriod(WorktimePeriod $period): self {
        if (!$this->periods->contains($period)) {
            $this->periods->add($period);
        }
        return $this;
    }

    /**
     * Removes a period from employee
     *
     * @param WorktimePeriod $period The period that should be removed
     * @return $this The updated entity
     */
    public function removePeriod(WorktimePeriod $period): self {
        if ($this->periods->contains($period)) {
            $this->periods->removeElement($period);
        }
        return $this;
    }

    /**
     * Gets the holidays of the user
     *
     * @return int|null The amount of holidays
     */
    public function getHolidays(): ?int
    {
        return $this->holidays;
    }

    /**
     * Sets the holidays of the user
     *
     * @param int $holidays The amount of holidays
     * @return $this The updated entity
     */
    public function setHolidays(int $holidays): self {
        $this->holidays = $holidays;
        return $this;
    }

    /**
     * Gets all worktime special days
     *
     * @return Collection The collection
     */
    public function getWorktimeSpecialDays(): Collection {
        return $this->worktimeSpecialDays;
    }

    /**
     * Adds a new special day
     *
     * @param WorktimeSpecialDay $worktimeSpecialDay The special day
     * @return $this The updated entity
     */
    public function addWorktimeSpecialDay(WorktimeSpecialDay $worktimeSpecialDay): self {
        if (!$this->worktimeSpecialDays->contains($worktimeSpecialDay)) {
            $this->worktimeSpecialDays->add($worktimeSpecialDay);
        }
        return $this;
    }

    /**
     * Removes a special worktime day
     *
     * @param WorktimeSpecialDay $worktimeSpecialDay The special day to remove
     * @return $this The updated entity
     */
    public function removeWorktimeSpecialDay(WorktimeSpecialDay $worktimeSpecialDay): self {
        if ($this->worktimeSpecialDays->contains($worktimeSpecialDay)) {
            $this->worktimeSpecialDays->removeElement($worktimeSpecialDay);
        }
        return $this;
    }

    /**
     * Gets all leftover holidays
     *
     * @return int|null All left holidays
     */
    public function getHolidaysLeft(): ?int
    {
        if ($this->getHolidays()) {
            $existingHolidays = $this->getWorktimeSpecialDays()
                ->filter(
                    fn (WorktimeSpecialDay $d) =>
                        $d->getDate()->format("Y") === (new DateTime())->format("Y")
                        && $d->getReason() === WorktimeSpecialDay::REASON_HOLIDAY
                );
            return $this->getHolidays() - $existingHolidays->count();
        }
        return null;
    }

    /**
     * Gets the configured worktimes
     *
     * @return Collection The worktimes
     */
    public function getConfiguredWorktimes(): Collection
    {
        return $this->configuredWorktimes;
    }

    /**
     * Sets the configured worktimes
     *
     * @param Collection $worktimes The worktimes
     * @return $this The updated entity
     */
    public function setConfiguredWorktimes(Collection $worktimes): self
    {
        $this->configuredWorktimes = $worktimes;
        return $this;
    }

    /**
     * Adds a new configured worktime
     *
     * @param ConfiguredWorktime $configuredWorktime The new time
     * @return $this The updated entity
     */
    public function addConfiguredWorktime(ConfiguredWorktime $configuredWorktime): self
    {
        $this->configuredWorktimes->add($configuredWorktime);
        return $this;
    }

    /**
     * Removes a configured worktime
     *
     * @param ConfiguredWorktime $configuredWorktime The removed worktime
     * @return $this The updated entity
     */
    public function removeConfiguredWorktime(ConfiguredWorktime $configuredWorktime): self
    {
        if ($this->configuredWorktimes->contains($configuredWorktime)) {
            $this->configuredWorktimes->removeElement($configuredWorktime);
        }
        return $this;
    }
}
