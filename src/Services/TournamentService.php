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
        if (count($submited) > $tournament->getMaxParticipantNumber()){
            return false;
        }

        /* @var TFUserRepository $repo */
        $repo = $this->entityManager->getRepository(TFUser::class);
        $players = $repo->findUsersByArrayEmail($submited);

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
        return true;
    }

    public function addTournamentParticipant(TFUser $player, TFTournament $tournament)
    {
        $users = $tournament->getPlayers();
        if (count($users) + 1 > $tournament->getMaxParticipantNumber()){
            return false;
        }

        $tournaments = $player->getTournaments();

        if (!$tournaments->contains($tournament)) {
            $player->addTournaments($tournament);
            $this->entityManager->persist($player);
        }
        $this->entityManager->flush();
        return true;

    }

    public function removeTournamentParticipant(TFUser $player, TFTournament $tournament)
    {
        $player->removeTournament($tournament);
        $this->entityManager->persist($player);
        $this->entityManager->flush();
        return true;
    }


}