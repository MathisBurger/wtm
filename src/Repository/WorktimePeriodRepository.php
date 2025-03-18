<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\WorktimePeriod;
use App\Utility\DateUtility;
use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
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
     * Finds all active to auto logout
     *
     * @return array The auto logout data
     */
    public function findToAutoLogoutByThreshold(): array
    {
        $date = new DateTime();
        $qb = $this->createQueryBuilder('p');
        $qb->join('p.employee', 'e');
        $qb->where($qb->expr()->isNull('p.endTime'));
        $qb->andWhere($qb->expr()->lt('e.autoLogoutThreshold', ':lessDate'));
        $qb->setParameter('lessDate', $date, Types::TIME_MUTABLE);
        return $qb->getQuery()->getResult();
    }

    /**
     * Finds all available periods (month + year)
     *
     * @return array
     * @throws \DateMalformedStringException
     */
    public function findPeriods(): array
    {
        $minMax = $this->getMinMaxQuery();
        $qb = $this->createQueryBuilder('p');
        /** @var WorktimePeriod[] $result */
        $result = $qb->where(
            $qb->expr()->in('p.id', $minMax)
        )->getQuery()->getResult();
        usort(
            $result,
            fn (WorktimePeriod $a, WorktimePeriod $b) => $a->getStartTime()->format('Y-m') <=> $b->getStartTime()->format('Y-m')
        );
        $start = DateTime::createFromInterface($result[0]->getStartTime());
        $start->setDate($start->format('Y'), $start->format('m'), 1);
        $end = $result[1]->getStartTime()->format('Y-m');
        if ($start->format('Y-m') === $end) {
            return [$start];
        }
        $periods = [];
        while ($start->format('Y-m') !== $end) {
            $periods[] = clone $start;
            $start->add(new \DateInterval('P1M'));
        }

        $latestPeriod = $this->getEntityManager()->getRepository(Employee::class)->findOneBy(['isTimeEmployed' => true])->getOvertimeTransfers();
        $periods[] = $start;
        $filteredPeriods =  array_filter(
            $periods,
            fn (DateTime $period) => isset($latestPeriod[$period->format("Y-m")])
        );

        if (count($filteredPeriods) > 0) {
            $lastPeriod = $filteredPeriods[array_keys($filteredPeriods)[count($filteredPeriods)-1]];
            if ($lastPeriod != (new DateTime())->format("Y-m")) {
                $filteredPeriods[] = DateUtility::getNextMonth($lastPeriod);
            }
        }
        return array_filter(
            $filteredPeriods,
            fn (DateTime $period) => $period->format("Y-m") !== (new DateTime())->format("Y-m")
        );
    }

    /**
     * Finds with username and date restrictions
     *
     * @param string $username The username
     * @param DateTimeInterface $lower The lower bound
     * @param DateTimeInterface $upper The upper bound
     * @return array
     */
    public function findForUserWithRestriction(string $username, DateTimeInterface $lower, DateTimeInterface $upper): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->join('p.employee', 'e');
        $qb->where($qb->expr()->eq('e.username', ':username'));
        $qb->andWhere('p.startTime BETWEEN :from AND :to');
        $qb->orderBy('p.startTime', 'ASC');
        $qb->setParameter('username', $username);
        $qb->setParameter('from', $lower);
        $qb->setParameter('to', $upper);
        return $qb->getQuery()->getResult();
    }

    /**
     * Finds with username and date restrictions
     *
     * @param string $username The username
     * @param DateTimeInterface $upper The upper bound
     * @return array
     */
    public function findForUserWithRestrictionUpperOnly(string $username, DateTimeInterface $upper): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->join('p.employee', 'e');
        $qb->where($qb->expr()->eq('e.username', ':username'));
        $qb->andWhere($qb->expr()->lt('p.startTime', ':to'));
        $qb->orderBy('p.startTime', 'ASC');
        $qb->setParameter('username', $username);
        $qb->setParameter('to', $upper, Types::DATETIME_MUTABLE);
        return $qb->getQuery()->getResult();
    }

    /**
     * Finds all entries for period
     *
     * @param int $year The year
     * @param int $month The month
     * @return array All entries
     */
    public function findForPeriod(int $year, int $month): array
    {
        $lowerBound = new DateTime();
        $lowerBound->setDate($year, $month, 1)->setTime(0, 0);
        $upperBound = new DateTime();
        $upperBound->setDate($year, $month, DateUtility::getMonthMaxDay($year, $month))->setTime(23, 59);;
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.startTime BETWEEN :from AND :to');
        $qb->orderBy('p.startTime', 'ASC');
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
