<?php

namespace App\Repository;

use App\Entity\WorktimePeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository to handle worktime periods
 *
 * @method WorktimePeriod|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorktimePeriod|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorktimePeriod|null findOneById(int $id, array $orderBy = null)
 * @method WorktimePeriod[]    findAll()
 * @method WorktimePeriod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorktimePeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorktimePeriod::class);
    }
}
