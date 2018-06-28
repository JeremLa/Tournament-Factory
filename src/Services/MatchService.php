<?php
/**
 * Created by PhpStorm.
 * User: AHermes
 * Date: 27/06/2018
 * Time: 16:57
 */

namespace App\Services;


use App\Entity\TFMatch;
use App\Entity\TFTournament;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\VarDumper\VarDumper;

class MatchService
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generateMatches(TFTournament $tournament)
    {

        $nbRound = $this->getMaxTurnInTournament($tournament);

        $match = new TFMatch();
        $match->setTurn(0);
        $match->setTournament($tournament);
        $tournament->addMatch($match);
     //   $this->entityManager->persist($match);
        $this->generate($tournament, $match, 1, $nbRound);

        $this->entityManager->persist($tournament);
        $this->entityManager->flush();
    }

    private function generate(TFTournament $tournament, TFMatch $nextMatch, int $round, int $nbRoundMax)
    {
        if($round < $nbRoundMax) {
            for($i=0; $i < 2 ; $i++) {
                $match = new TFMatch();
                $match->setTurn($round);
                $match->setTournament($tournament);
                $match->setNextMatch($nextMatch);
                $tournament->addMatch($match);
          //      $this->entityManager->persist($match);
                $this->generate($tournament, $match, $round + 1, $nbRoundMax);
            }
        }
        return;
    }

    /**
     * @param Collection $matches
     * @return array
     */
    private function getRoundList(Collection $matches)
    {
        $rounds = [];
        /** @var  TFMatch $match */
        foreach ($matches as $match){
            $rounds[] = $match->getTurn();
        }

        return array_unique($rounds);
    }

    public function getMaxTurnInTournament(TFTournament $tournament)
    {
        $participantNumber = $tournament->getMaxParticipantNumber();
        return log($participantNumber ,2);
    }

    public function getMatchPerRound(TFTournament $tournament)
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
}