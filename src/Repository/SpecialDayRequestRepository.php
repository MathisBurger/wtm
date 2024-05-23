<?php

namespace App\Repository;

use App\Entity\SpecialDayRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository to handle special day requests
 *
 * @method SpecialDayRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpecialDayRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpecialDayRequest|null findOneById(int $id, array $orderBy = null)
 * @method SpecialDayRequest[]    findAll()
 * @method SpecialDayRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecialDayRequestRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpecialDayRequest::class);
    }

}