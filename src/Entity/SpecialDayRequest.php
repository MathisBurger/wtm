<?php

namespace App\Entity;

use App\Repository\SpecialDayRequestRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Worktime special day entity
 */
#[ORM\Entity(repositoryClass: SpecialDayRequestRepository::class)]
class SpecialDayRequest extends AbstractEntity
{

    /**
     * Illness as reason
     */
    public const REASON_ILLNESS = 'ILLNESS';

    /**
     * Holiday as reason
     */
    public const REASON_HOLIDAY = 'HOLIDAY';

    /**
     * The reason of special day request
     */
    #[ORM\Column(length: 255)]
    private ?string $reason = null;

    /**
     * The date of the day
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $date = null;

    /**
     * Notes of the day
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: Employee::class, inversedBy: "specialDayRequests")]
    #[ORM\JoinColumn(nullable: false, onDelete: 'cascade')]
    private ?Employee $employee = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $documentFileName = null;

    /**
     * Gets the reason for the special day
     *
     * @return string|null The reason
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Sets the reason
     *
     * @param string $reason The reason
     * @return $this The updated entity
     */
    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Gets the date of the day
     *
     * @return DateTimeInterface|null The date
     */
    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Sets the date of the day
     *
     * @param DateTimeInterface $date The new date
     * @return $this The updated entity
     */
    public function setDate(DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Gets the notes
     *
     * @return string|null The notes
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * Sets the notes
     *
     * @param string|null $notes The new notes
     * @return $this The updated entity
     */
    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Gets the employee
     *
     * @return Employee|null The employee
     */
    public function getEmployee(): ?Employee {
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
     * Gets the date formatted as string
     *
     * @return string The date string
     */
    public function getDateString(): string
    {
        if (null === $this->date) {
            return "";
        }
        return $this->date->format('d.m.y');
    }

    /**
     * Gets the document file name
     *
     * @return string|null The file name
     */
    public function getDocumentFileName(): ?string
    {
        return $this->documentFileName;
    }

    /**
     * Sets the document file name
     *
     * @param string|null $fileName The file name
     * @return $this The updated entity
     */
    public function setDocumentFileName(?string $fileName): self
    {
        $this->documentFileName = $fileName;
        return $this;
    }

}