<?php

namespace App\Services;


use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Repository\TFUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\VarDumper\VarDumper;

class TournamentService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateTournamentParticipant(array $submited, TFTournament $tournament)
    {
        $players_id = [];
        if (array_key_exists('players', $submited)) {
            $players_id = $submited['players'];
        }

        /* @var TFUserRepository $repo */
        $repo = $this->entityManager->getRepository(TFUser::class);
        $players = $repo->findUsersByArrayId($players_id);

        $players_to_delete = array_diff($tournament->getPlayers()->toArray(), $players);

        /* @var TFUser $player */
        foreach ($players_to_delete as $player) {
            $player->removeTournament($tournament);
            $this->entityManager->persist($player);
        }

        foreach ($players as $player) {
            $tournaments = $player->getTournaments();

            if (!$tournaments->contains($tournament)) {
                $player->addTournaments($tournament);
                $this->entityManager->persist($player);
            }
        }
        $this->entityManager->flush();
    }
}