<?php

namespace App\Repository;

use App\Entity\Alert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PushReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method PushReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method PushReport[]    findAll()
 * @method PushReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alert::class);
    }

     /*
     * Query che ritorna tutti i gruppi
     */
    public function findByDate(int $typeQuery, string $limit = null, string $sort = 'desc')
    {

        $select = 'a';
        if ($typeQuery == Alert::COUNT_QUERY) {
            $select = 'count(a) as tot';
        }

        $query = $this->getEntityManager()->createQuery(
            "SELECT $select FROM App:Alert a ORDER BY a.createdAt $sort"
        );

        if ($limit !== null) {
            $aLimit = explode(',', $limit);
            $query->setFirstResult((int)$aLimit[0])->setMaxResults((int)$aLimit[1]);
        }


        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return $e;
        }
    }
}
