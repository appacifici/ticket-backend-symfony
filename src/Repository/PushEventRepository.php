<?php

namespace App\Repository;

use App\Entity\PushEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PushEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method PushEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method PushEvent[]    findAll()
 * @method PushEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PushEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PushEvent::class);
    }

    public function getPushEvent(int $pushId): ?PushEvent {

        $sql = "
            SELECT e FROM App\Entity\PushEvent e            
            WHERE e.pushId = $pushId AND e.processed = 0 AND e.toBeProcessed = 0
        ";

        $query = $this->getEntityManager()
                ->createQuery($sql);        
        
        $result = $query->getOneOrNullResult();
        return $result;
    }
}
