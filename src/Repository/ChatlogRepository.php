<?php

namespace App\Repository;

use App\Entity\Chatlog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Chatlog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chatlog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chatlog[]    findAll()
 * @method Chatlog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chatlog::class);
    }

    // /**
    //  * @return Chatlog[] Returns an array of Chatlog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Chatlog
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
