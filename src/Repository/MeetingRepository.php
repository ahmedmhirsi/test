<?php

namespace App\Repository;

use App\Entity\Meeting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Meeting>
 */
class MeetingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meeting::class);
    }

    /**
     * Find upcoming meetings
     */
    public function findUpcomingMeetings(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.date_debut > :now')
            ->andWhere('m.statut = :statut')
            ->setParameter('now', new \DateTime())
            ->setParameter('statut', 'Planifié')
            ->orderBy('m.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find meetings in progress
     */
    public function findInProgressMeetings(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.statut = :statut')
            ->setParameter('statut', 'En cours')
            ->orderBy('m.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find meetings by status
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('m.date_debut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find meetings for today
     */
    public function findTodayMeetings(): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('m')
            ->andWhere('m.date_debut >= :today')
            ->andWhere('m.date_debut < :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->orderBy('m.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find meetings within a date range
     */
    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.date_debut >= :start')
            ->andWhere('m.date_debut <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('m.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find meetings for a specific month
     */
    public function findByMonth(int $year, int $month): array
    {
        $start = new \DateTime("$year-$month-01 00:00:00");
        $end = (clone $start)->modify('last day of this month')->setTime(23, 59, 59);

        return $this->findByDateRange($start, $end);
    }

    /**
     * Find meetings for a specific week
     */
    public function findByWeek(int $year, int $week): array
    {
        $start = new \DateTime();
        $start->setISODate($year, $week, 1)->setTime(0, 0, 0);
        
        $end = (clone $start)->modify('+6 days')->setTime(23, 59, 59);

        return $this->findByDateRange($start, $end);
    }
}
