<?php

namespace App\Services;


use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Repository\TFTournamentRepository;
use App\Repository\TFUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
            $tournament->removePlayer($player);
            $this->entityManager->persist($player);
            $this->entityManager->persist($tournament);
        }

        foreach ($players as $player) {
            $tournaments = $player->getTournaments();

            if (!$tournaments->contains($tournament)) {
                $player->addTournaments($tournament);
                $tournament->addPlayer($player);
                $this->entityManager->persist($tournament);
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
            $tournament->addPlayer($player);
            $this->entityManager->persist($player);
            $this->entityManager->persist($tournament);
        }
        $this->entityManager->flush();
        return true;

    }

    public function removeTournamentParticipant(TFUser $player, TFTournament $tournament)
    {
        $player->removeTournament($tournament);
        $tournament->removePlayer($player);
        $this->entityManager->persist($player);
        $this->entityManager->persist($tournament);
        $this->entityManager->flush();
        return true;
    }

    public function checkTournamentExist($tournamentId) : TFTournament{
        /* @var TFTournamentRepository $repo */
        $repo = $this->entityManager->getRepository(TFTournament::class);
        $tournament = $repo->find($tournamentId);

        if($tournament == null){
            throw new NotFoundHttpException("Ce tournoi n'existe pas");
        }
        return $tournament;
    }

}