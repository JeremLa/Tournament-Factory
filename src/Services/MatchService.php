<?php
/**
 * Created by PhpStorm.
 * User: AHermes
 * Date: 27/06/2018
 * Time: 16:57
 */

namespace App\Services;


use App\Entity\TFMatch;
use App\Entity\TFTeam;
use App\Entity\TFTournament;
use App\Entity\TFUser;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\VarDumper\VarDumper;

class MatchService
{

    private $entityManager;
    private const SCORE = 'score';

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generateMatches(TFTournament $tournament, bool $withParticipantAssignement = false) : void
    {
        $nbRound = $this->getMaxTurnInTournament($tournament);

        $match = new TFMatch();
        $match->setTurn(0);
        $match->setTournament($tournament);
        $tournament->addMatch($match);
        $this->generate($tournament, $match, 1, $nbRound);

        if($withParticipantAssignement) {
            $this->assignParticipants($tournament);
        }

        $this->entityManager->persist($tournament);
        $this->entityManager->flush();
    }

    private function generate(TFTournament $tournament, TFMatch $nextMatch, int $round, int $nbRoundMax) : void
    {
        if($round < $nbRoundMax) {
            for($i=0; $i < 2 ; $i++) {
                $match = new TFMatch();
                $match->setTurn($round);
                $match->setTournament($tournament);
                $match->setNextMatch($nextMatch);
                $tournament->addMatch($match);
                $this->generate($tournament, $match, $round + 1, $nbRoundMax);
            }
        }
    }

    /**
     * @param Collection $matches
     * @return array
     */
    private function getRoundList(Collection $matches) : array
    {
        $rounds = [];
        /** @var  TFMatch $match */
        foreach ($matches as $match){
            $rounds[] = $match->getTurn();
        }

        return array_unique($rounds);
    }

    public function getMaxTurnInTournament(TFTournament $tournament) : int
    {
        $participantNumber = $tournament->getMaxParticipantNumber();
        return (int) log($participantNumber ,2);
    }

    public function getMatchPerRound(TFTournament $tournament) : array
    {
        $array = [];
        $repo = $this->entityManager->getRepository(TFMatch::class);
        $rounds = $this->getRoundList($tournament->getMatches());
        foreach($rounds as $round){
            $array [$round] = $repo->findBy([
                'tournament' => $tournament,
                'turn' => $round
            ]);
        }
        return $array;
    }

    public function assignParticipants (TFTournament $tournament) : void
    {
        $used = [];

        $maxTurn = $this->getMaxTurnInTournament($tournament) - 1;

        /* @var TFMatch $match */
        foreach ($tournament->getMatches() as $match) {
            if($match->getTurn() === $maxTurn){
                $participant1 = $this->randomUser($tournament->getPlayers()->toArray(), $used);
                $used[] = $participant1;
                $participant2 = $this->randomUser($tournament->getPlayers()->toArray(), $used);
                $used[] = $participant2;

                $this->assignPlayers($match, $participant1, $participant2);
            }
        }
    }

    /**
     * @param TFMatch $match
     * @param TFUser $participant1
     * @param TFUser $participant2
     */
    public function assignPlayers (TFMatch $match, TFUser $participant1, TFUser $participant2) : void
    {
            $match->setScore([
               $participant1->getId() => 0,
               $participant2->getId() => 0
            ]);
            $match->addPlayer($participant1)
                  ->addPlayer($participant2);
    }

    /**
     * @param TFMatch $match
     * @param TFTeam $participant1
     * @param TFTeam $participant2
     */
    public function addTeams (TFMatch $match, TFTeam $participant1, TFTeam $participant2) : void
    {
        $match->addTeam($participant1)
              ->addTeam($participant2);
    }

    public function getScoreForForm (TFMatch $match) : array
    {
        $result = [];
        $index = 1;

        foreach ($match->getPlayers() as $player) {
            $result[self::SCORE.$index] = 0;
            if(isset($match->getScore()[$player->getId()])) {
                $result[self::SCORE.$index] = $match->getScore()[$player->getId()];
            }
            $index++;
        }
        return $result;
    }

    public function updateScore(TFMatch $match, FormInterface $scoreForm)
    {
        $index = 1;

        foreach ($match->getPlayers() as $player) {
            $score = $scoreForm->get(self::SCORE.$index)->getData() ?: 0;
            $match->setScore($player->getId(), $score);

            $index++;
        }

        $this->entityManager->persist($match);
        $this->entityManager->flush();

        return $match;
    }

    private function randomUser (array $users, array $excluded = []) : TFUser
    {
        $min = 0;
        $max = count($users) - 1;
        $find = false;
        $rand = 0;

        while (!$find) {
            $rand = random_int($min, $max);

            echo 'random : '.$rand;

            if(!in_array($users[$rand], $excluded)){
                $find = true;
            }
        }

        return $users[$rand];
    }
}