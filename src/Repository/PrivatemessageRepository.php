<?php

namespace App\Repository;

use App\Entity\Privatemessages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Privatemessages|null find($id, $lockMode = null, $lockVersion = null)
 * @method Privatemessages|null findOneBy(array $criteria, array $orderBy = null)
 * @method Privatemessages[]    findAll()
 * @method Privatemessages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrivatemessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Privatemessages::class);
    }

    // /**
    //  * @return PrivateChat[] Returns an array of PrivateChat objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    public function findChatFrom($uuid)
    {
        return $this->createQueryBuilder('p')
            ->orWhere('p.Sender = :val')
            ->orWhere('p.Receiver = :val')
            ->setParameter('val', $uuid)
            ->orderBy('p.Date', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
