<?php

namespace App\Repository;

use App\Entity\Tokens;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tokens|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tokens|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tokens[]    findAll()
 * @method Tokens[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TokensRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tokens::class);
    }

    // /**
    //  * @return Tokens[] Returns an array of Tokens objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Tokens
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
