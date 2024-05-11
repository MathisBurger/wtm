<?php

namespace App\Repository;

use App\Entity\WorktimeSpecialDay;
use App\Utility\DateUtility;
use DateTime;
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

    /**
     * Finds all entries for period
     *
     * @param string $period The period as string
     * @return array All entries
     */
    public function findForPeriod(string $period): array
    {
        $spl = explode("-", $period);
        if (count($spl) !== 2) {
            return [];
        }
        $year = intval($spl[0]);
        $month = intval($spl[1]);
        if ($year === 0 || $month === 0) {
            return [];
        }
        $lowerBound = new DateTime();
        $lowerBound->setDate($year, $month, 1)->setTime(0, 0);
        $upperBound = new DateTime();
        $upperBound->setDate($year, $month, DateUtility::getMonthMaxDay($year, $month))->setTime(23, 59);;
        $qb = $this->createQueryBuilder('d');
        $qb->where('d.date BETWEEN :from AND :to');
        $qb->setParameter('from', $lowerBound);
        $qb->setParameter('to', $upperBound);
        return $qb->getQuery()->getResult();
    }
}
