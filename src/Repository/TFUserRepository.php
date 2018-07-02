<?php

namespace App\Repository;

use App\Entity\TFTournament;
use App\Entity\TFUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @method TFUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method TFUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method TFUser[]    findAll()
 * @method TFUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TFUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TFUser::class);
    }

    public function findUsersByArrayId(array $arrayId)
    {
        return $this->createQueryBuilder('TFU')
                ->where('TFU.id IN (:array)')
                ->setParameter('array',$arrayId)
                ->getQuery()
                ->getResult();
    }
    public function findUsersByArrayEmail(array $arrayEmail)
    {
        return $this->createQueryBuilder('TFU')
            ->where('TFU.email IN (:array)')
            ->setParameter('array',$arrayEmail)
            ->getQuery()
            ->getResult();
    }

    public function getUsersNotInTournament(TFTournament $tournament, string $search)
    {
        if($tournament->getPlayers()->isEmpty()){
            return $this->createQueryBuilder('users')
                ->where('users.email LIKE :email')
                ->setParameter('email', '%'.$search.'%')
                ->getQuery()
                ->getResult();
        }
        return $this->createQueryBuilder('users')
            ->where('users.id NOT IN (:tournament)')
            ->andWhere('users.email LIKE :email')
            ->setParameter('tournament', $tournament->getPlayers()->toArray())
            ->setParameter('email', '%'.$search.'%')
            ->getQuery()
            ->getResult();

    }
}
