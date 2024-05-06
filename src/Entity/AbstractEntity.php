<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Abstract entity
 */
#[ORM\MappedSuperclass]
class AbstractEntity
{
    /**
     * The ID of the entity
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;


    /**
     * Gets the ID of the entity
     *
     * @return int|null The ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

}