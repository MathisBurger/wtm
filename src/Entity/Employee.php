<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The employee entity that contains all data
 */
#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
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
     * Target working time begin
     */
    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $targetWorkingTimeBegin = null;

    /**
     * Target working time end
     */
    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $targetWorkingTimeEnd = null;

    /**
     * If the user has target working enabled
     */
    #[ORM\Column]
    private ?bool $targetWorkingPresent = null;

    /**
     * All working periods of the user
     */
    #[ORM\OneToMany(targetEntity: WorktimePeriod::class, mappedBy: 'employee')]
    private Collection $periods;

    public function __construct() {
        $this->periods = new ArrayCollection();
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
    public function setTargetWorkingHours(float $targetWorkingHours): self
    {
        $this->targetWorkingHours = $targetWorkingHours;

        return $this;
    }

    /**
     * Gets the target working time begin
     *
     * @return float|null target working time begin
     */
    public function getTargetWorkingTimeBegin(): ?DateTimeInterface
    {
        return $this->targetWorkingTimeBegin;
    }

    /**
     * Sets the target working time begin
     *
     * @param float $targetWorkingTimeBegin The target working time
     * @return $this The updated entity
     */
    public function setTargetWorkingTimeBegin(?DateTimeInterface $targetWorkingTimeBegin): self
    {
        $this->targetWorkingTimeBegin = $targetWorkingTimeBegin;

        return $this;
    }

    /**
     * Gets the target working time end
     *
     * @return float|null The target working time end
     */
    public function getTargetWorkingTimeEnd(): ?DateTimeInterface
    {
        return $this->targetWorkingTimeEnd;
    }

    /**
     * Sets the target working time end
     *
     * @param float $targetWorkingTimeEnd The target working end
     * @return $this The updated entity
     */
    public function setTargetWorkingTimeEnd(?DateTimeInterface $targetWorkingTimeEnd): self
    {
        $this->targetWorkingTimeEnd = $targetWorkingTimeEnd;

        return $this;
    }

    /**
     * Checks if target working time is enabled
     *
     * @return bool|null If enabled
     */
    public function isTargetWorkingPresent(): ?bool
    {
        return $this->targetWorkingPresent;
    }

    /**
     * Sets if the target working time
     *
     * @param bool $targetWorkingPresent The status
     * @return $this The updated entity
     */
    public function setTargetWorkingPresent(bool $targetWorkingPresent): self
    {
        $this->targetWorkingPresent = $targetWorkingPresent;

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
}
