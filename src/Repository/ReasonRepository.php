<?php

namespace App\Repository;

use App\Entity\Reasons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reasons|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reasons|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reasons[]    findAll()
 * @method Reasons[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reasons::class);
    }

    // /**
    //  * @return Reasons[] Returns an array of Reasons objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Reasons
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
