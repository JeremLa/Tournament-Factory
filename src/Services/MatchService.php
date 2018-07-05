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
use App\Services\Enum\TournamentStatusEnum;
use App\Services\Enum\TournamentTypeEnum;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\VarDumper\VarDumper;

class MatchService
{

    /* @var Session $session */
    private $session;
    private $entityManager;
    private const SCORE = 'score';
    private const MESSAGE_TYPE_WARNING = 'warning';

    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    public function findOr404 ($id) : TFMatch
    {
        $match = $this->entityManager->getRepository('App:TFMatch')->find($id);
        if(!$match) {
            throw new NotFoundHttpException('Ce match n\'existe pas.');
        }

        return $match;
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

        $players = $tournament->getPlayers()->toArray();

        /* @var TFMatch $match */
        foreach ($tournament->getMatches() as $match) {
            if($match->getTurn() === $maxTurn){
                $participant1 = $this->randomUser($players, $used);
                $used[] = $participant1;
                $participant2 = $this->randomUser($players, $used);
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
            $match->addPlayer($participant1)
                  ->addPlayer($participant2);
            $this->initScores($match);
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

    /**
     * @param TFMatch $match
     * @return array
     */
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

    /**
     * @param TFMatch $match
     * @param array $score
     * @param bool $isOver
     * @return TFMatch
     */
    public function updateScore(TFMatch $match,array $score, bool $isOver)
    {
            if ($this->canBeUpdated($match)) {
                $hasNoEquality = $this->hasNoEquality($score[0], $score[1]);
                if (!$hasNoEquality) {
                    $canHaveEquality = $this->canHaveEquality($match);
                    if (!$canHaveEquality) {
                        $this->addFlashMessage('match.singleElimination.over.notEqual');
                    }
                }

                $hasNoNegativeScore = $this->hasNoNegativeScore($score[0], $score[1]);
                if (!$hasNoNegativeScore) {
                    $this->addFlashMessage('match.singleElimination.over.lowerThanZero');
                }

                $index = 0;

                foreach ($match->getPlayers() as $player) {
                    $match->setScore($player->getId(), $score[$index]);

                    $index++;
                }

                if ($isOver && $hasNoEquality && $hasNoNegativeScore) {
                    $this->updateNextMatch($match);
                    $match->setOver(true);
                }

                $this->entityManager->persist($match);
                $this->entityManager->flush();
            }

        return $match;
    }

    /**
     * @param array $users
     * @param array $excluded
     * @return TFUser
     * @throws \Exception
     */
    private function randomUser (array $users, array $excluded = []) : TFUser
    {
        $min = 0;
        $max = count($users) - 1;
        $find = false;
        $rand = 0;

        while (!$find) {
            $rand = random_int($min, $max);

            if(!in_array($users[$rand], $excluded)){
                $find = true;
            }
        }

        return $users[$rand];
    }

    /**
     * @param int $score1
     * @param int $score2
     * @return bool
     */
    public function hasNoNegativeScore (int $score1, int $score2) : bool
    {
        $return = false;

        if($score1 >= 0 && $score2 >= 0) {
            $return = true;
        }

        return $return;
    }

    /**
     * @param int $score1
     * @param int $score2
     * @return bool
     */
    public function hasNoEquality (int $score1, int $score2) : bool
    {
        $return = false;
        if($score1 !== $score2) {
            $return = true;
        }

        return $return;
    }

    /**
     * @param TFMatch $match
     * @return bool
     */
    public function canHaveEquality (TFMatch $match) : bool
    {
        /* @var TFTournament $tournament */
        $tournament = $match->getTournament();

        switch ($tournament->getType())
        {
            case TournamentTypeEnum::TYPE_SINGLE :
                return false;
            default:
                return false;
        }
    }

    /**
     * @param TFMatch $match
     * @return bool
     */
    public function canBeUpdated (TFMatch $match) : bool
    {
        if(!$match->getPlayers()->isEmpty()) {
            if (!$match->isOver()) {
                return true;
            }else{
                $this->addFlashMessage('match.singleElimination.over.update', true, 'danger');
            }
        }else{
            $this->addFlashMessage('match.singleElimination.over.noPlayers', true, 'danger');
        }
        return false;
    }

    public function updateNextMatch (TFMatch $match)
    {
        $nextMatch = $match->getNextMatch();

        if(!$nextMatch){
            $match->getTournament()->setStatus(TournamentStatusEnum::STATUS_FINISHED);
            return;
        }

        $userId = array_search(max($match->getScore()), $match->getScore());

        $user = null;
        foreach ($match->getPlayers() as $player) {
            if ($player->getId() == $userId) {
                $user = $player;
                break;
            }
        }
        if($user) {
            $nextMatch->addPlayer($user);
            $this->initScores($nextMatch);
            $this->entityManager->persist($nextMatch);
            $this->entityManager->flush();
        }
    }

    /**
     * @param TFMatch $match
     */
    public function initScores (TFMatch $match) {
        $match->setScores([]);
        foreach ($match->getPlayers() as $player) {
            $match->setScore($player->getId(), 0);
        }
    }

    /**
     * @param string $state
     * @param string $message
     * @param bool $withMessage
     */
    private function addFlashMessage (string $message,bool $withMessage = true, string $state = self::MESSAGE_TYPE_WARNING) : void
    {
        if($withMessage) {
            $this->session->getFlashBag()->add($state, $message);
        }
    }
}
