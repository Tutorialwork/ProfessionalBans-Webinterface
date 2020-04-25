<?php

namespace App\Repository;

use App\Entity\Unbans;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Unbans|null find($id, $lockMode = null, $lockVersion = null)
 * @method Unbans|null findOneBy(array $criteria, array $orderBy = null)
 * @method Unbans[]    findAll()
 * @method Unbans[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnbansRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unbans::class);
    }

    // /**
    //  * @return Unbans[] Returns an array of Unbans objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Unbans
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
