<?php

namespace App\Repository;

use App\Entity\WorktimeSpecialDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository to handle worktime special days
 *
 * @method WorktimeSpecialDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorktimeSpecialDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorktimeSpecialDay|null findOneById(int $id, array $orderBy = null)
 * @method WorktimeSpecialDay[]    findAll()
 * @method WorktimeSpecialDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorktimeSpecialDayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorktimeSpecialDay::class);
    }
}
