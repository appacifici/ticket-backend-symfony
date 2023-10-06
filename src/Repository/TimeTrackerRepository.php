<?php

namespace App\Repository;

use App\Entity\TimeTracker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeTracker>
 *
 * @method TimeTracker|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeTracker|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeTracker[]    findAll()
 * @method TimeTracker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeTrackerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeTracker::class);
    }

    public function save(TimeTracker $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();
        }
    }

    public function remove(TimeTracker $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();
        }
    }

//    /**
//     * @return TimeTracker[] Returns an array of TimeTracker objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TimeTracker
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
