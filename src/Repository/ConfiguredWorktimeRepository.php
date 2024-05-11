<?php

namespace App\Repository;

use App\Entity\ConfiguredWorktime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository to handle configured worktimes
 *
 * @method ConfiguredWorktime|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfiguredWorktime|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfiguredWorktime|null findOneById(int $id, array $orderBy = null)
 * @method ConfiguredWorktime[]    findAll()
 * @method ConfiguredWorktime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfiguredWorktimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfiguredWorktime::class);
    }
}
