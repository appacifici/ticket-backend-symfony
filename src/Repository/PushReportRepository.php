<?php

namespace App\Repository;

use App\Entity\PushReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<TimeTracker>
 *
 * @method TimeTracker|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeTracker|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeTracker[]    findAll()
 * @method TimeTracker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PushReportRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private Connection $connection
    )
    {
        parent::__construct($registry, PushReport::class);
    }

    public function save(PushReport $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();
        }
    }

    public function remove(PushReport $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();
        }
    }

    public function updateSetToProcessed() {

        $sql = '
            UPDATE App\Entity\PushReport p
            SET p.toBeProcessed = 1
            WHERE p.processed = 0
        ';

        $query = $this->getEntityManager()
                ->createQuery($sql);        
        
        $query->execute();
    }

    public function getPushReport(int $pushId): ?PushReport {

        $sql = "
            SELECT e FROM App\Entity\PushReport e            
            WHERE e.pushId = $pushId AND e.processed = 0 AND e.toBeProcessed = 0
        ";

        $query = $this->getEntityManager()
                ->createQuery($sql);        
        
        $result = $query->getOneOrNullResult();
        return $result;
    }
}
