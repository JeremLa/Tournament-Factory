<?php
namespace App\Repository;


use App\Entity\TFTournament;
use App\Entity\TFUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TFTournament|null find($id, $lockMode = null, $lockVersion = null)
 * @method TFTournament|null findOneBy(array $criteria, array $orderBy = null)
 * @method TFTournament[]    findAll()
 * @method TFTournament[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TFTournamentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TFTournament::class);
    }

    public function getMyTournaments (TFUser $user) {
        return $this->createQueryBuilder('tournament')
                ->leftJoin('tournament.players', 'p', 'p = :user')
                ->where('tournament.owner = :user')
                ->orWhere('p = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();
    }
}
