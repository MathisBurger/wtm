<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column]
    private ?float $targetWorkingHours = null;

    #[ORM\Column]
    private ?float $targetWorkingTimeBegin = null;

    #[ORM\Column]
    private ?float $targetWorkingTimeEnd = null;

    #[ORM\Column]
    private ?bool $targetWorkingPresent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getTargetWorkingHours(): ?float
    {
        return $this->targetWorkingHours;
    }

    public function setTargetWorkingHours(float $targetWorkingHours): static
    {
        $this->targetWorkingHours = $targetWorkingHours;

        return $this;
    }

    public function getTargetWorkingTimeBegin(): ?float
    {
        return $this->targetWorkingTimeBegin;
    }

    public function setTargetWorkingTimeBegin(float $targetWorkingTimeBegin): static
    {
        $this->targetWorkingTimeBegin = $targetWorkingTimeBegin;

        return $this;
    }

    public function getTargetWorkingTimeEnd(): ?float
    {
        return $this->targetWorkingTimeEnd;
    }

    public function setTargetWorkingTimeEnd(float $targetWorkingTimeEnd): static
    {
        $this->targetWorkingTimeEnd = $targetWorkingTimeEnd;

        return $this;
    }

    public function isTargetWorkingPresent(): ?bool
    {
        return $this->targetWorkingPresent;
    }

    public function setTargetWorkingPresent(bool $targetWorkingPresent): static
    {
        $this->targetWorkingPresent = $targetWorkingPresent;

        return $this;
    }
}
