<?php

namespace App\Repository;

use App\Entity\WorktimePeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository to handle worktime periods
 */
class WorktimePeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorktimePeriod::class);
    }
}
