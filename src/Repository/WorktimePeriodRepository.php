<?php

namespace App\Repository;

use App\Entity\WorktimePeriod;
use App\Utility\DateUtility;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Finds all available periods (month + year)
     *
     * @return array
     */
    public function findPeriods(): array
    {
        $minMax = $this->getMinMaxQuery();

        $qb = $this->createQueryBuilder('p');
        /** @var WorktimePeriod[] $result */
        $result = $qb->where(
            $qb->expr()->in('p.id', $minMax)
        )->getQuery()->getResult();
        $start = DateTime::createFromInterface($result[1]->getStartTime());
        $end = $result[0]->getStartTime()->format('Y-m');
        if ($start->format('Y-m') === $end) {
            return [$start];
        }
        $periods = [];
        while ($start->format('Y-m') !== $end) {
            $periods[] = clone $start;
            $start->add(new \DateInterval('P1M'));
        }
        $periods[] = $start;
        return $periods;
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
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.startTime BETWEEN :from AND :to');
        $qb->setParameter('from', $lowerBound);
        $qb->setParameter('to', $upperBound);
        return $qb->getQuery()->getResult();
    }


    /**
     * Gets the min max query
     */
    private function getMinMaxQuery(): array
    {
        $qb2 = $this->createQueryBuilder('p2')
            ->select('p2.id')
            ->orderBy('p2.startTime', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
        $qb3 = $this->createQueryBuilder('p3')
            ->select('p3.id')
            ->orderBy('p3.startTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return array_map(
            fn (array $arr) => $arr[0]['id'],
            [$qb2, $qb3]
        );
    }
}
