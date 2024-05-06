<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * The employee entity that contains all data
 */
#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
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
    #[ORM\Column]
    private ?float $targetWorkingHours = null;

    /**
     * Target working time begin
     */
    #[ORM\Column]
    private ?float $targetWorkingTimeBegin = null;

    /**
     * Target working time end
     */
    #[ORM\Column]
    private ?float $targetWorkingTimeEnd = null;

    /**
     * If the user has target working enabled
     */
    #[ORM\Column]
    private ?bool $targetWorkingPresent = null;

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
    public function getTargetWorkingTimeBegin(): ?float
    {
        return $this->targetWorkingTimeBegin;
    }

    /**
     * Sets the target working time begin
     *
     * @param float $targetWorkingTimeBegin The target working time
     * @return $this The updated entity
     */
    public function setTargetWorkingTimeBegin(float $targetWorkingTimeBegin): self
    {
        $this->targetWorkingTimeBegin = $targetWorkingTimeBegin;

        return $this;
    }

    /**
     * Gets the target working time end
     *
     * @return float|null The target working time end
     */
    public function getTargetWorkingTimeEnd(): ?float
    {
        return $this->targetWorkingTimeEnd;
    }

    /**
     * Sets the target working time end
     *
     * @param float $targetWorkingTimeEnd The target working end
     * @return $this The updated entity
     */
    public function setTargetWorkingTimeEnd(float $targetWorkingTimeEnd): self
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
}
