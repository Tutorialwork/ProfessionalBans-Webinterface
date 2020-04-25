<?php

namespace App\Repository;

use App\Entity\Bans;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Bans|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bans|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bans[]    findAll()
 * @method Bans[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BansRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bans::class);
    }

    // /**
    //  * @return Bans[] Returns an array of Bans objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Bans
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
