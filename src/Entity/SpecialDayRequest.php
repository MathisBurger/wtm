<?php

namespace App\Entity;

use App\Repository\SpecialDayRequestRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 *  Special day request entity
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
     * The start date of the day
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $startDate = null;

    /**
     * The start date of the day
     */
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $endDate = null;

    /**
     * Notes of the day
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: Employee::class, inversedBy: "specialDayRequests")]
    #[ORM\JoinColumn(nullable: false, onDelete: 'cascade')]
    private ?Employee $employee = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $documentFileName = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $respondEmail = null;

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
     * Gets the start date of the day
     *
     * @return DateTimeInterface|null The date
     */
    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * Sets the start date of the day
     *
     * @param DateTimeInterface $date The new date
     * @return $this The updated entity
     */
    public function setStartDate(DateTimeInterface $date): static
    {
        $this->startDate = $date;

        return $this;
    }

    /**
     * Gets the end date of the day
     *
     * @return DateTimeInterface|null The date
     */
    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * Sets the end date of the day
     *
     * @param DateTimeInterface $date The new date
     * @return $this The updated entity
     */
    public function setEndDate(DateTimeInterface $date): static
    {
        $this->endDate = $date;

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
    public function getStartDateString(): string
    {
        if (null === $this->startDate) {
            return "";
        }
        return $this->startDate->format('d.m.y');
    }

    /**
     * Gets the date formatted as string
     *
     * @return string The date string
     */
    public function getEndDateString(): string
    {
        if (null === $this->endDate) {
            return "";
        }
        return $this->endDate->format('d.m.y');
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

    /**
     * Gets the response email
     *
     * @return string|null The response email
     */
    public function getRespondEmail(): ?string
    {
        return $this->respondEmail;
    }

    /**
     * Sets the email
     *
     * @param string $email The email
     * @return $this The updated entity
     */
    public function setRespondEmail(string $email): self
    {
        $this->respondEmail = $email;
        return $this;
    }

}