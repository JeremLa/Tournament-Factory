<?php

namespace App\Repository;

use App\Entity\TFMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TFMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method TFMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method TFMatch[]    findAll()
 * @method TFMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TFMatchRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TFMatch::class);
    }

    /**
     * @return TFMatch[] Returns an array of TFMatch objects
     */
    public function findByUserParticipantNotOver($user)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.players', 'p')
            ->where('p = :user')
            ->andWhere('m.over = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return TFMatch[] Returns an array of TFMatch objects
     */
    public function findByUserParticipantOver($user)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.players', 'p')
            ->where('p = :user')
            ->andWhere('m.over = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
            ;
    }

}
