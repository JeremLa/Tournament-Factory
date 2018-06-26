<?php

namespace App\Services;


use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Services\Enum\TournamentStatusEnum;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TournamentRulesServices
{
    /* @var Session $session */
    private $session;
    const MESSAGE_TYPE_WARNING = 'warning';
    const MIN_PARTICIPANT_REQUIRED = 2;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Return if Tournament can be delete and push flash message if it is false
     *
     * @param TFTournament $TFTournament
     * @return bool
     */
    public function canBeDelete (TFTournament $TFTournament) : bool
    {
        $result = $this->isInSetup($TFTournament);

        if (!$result) {
            $this->addFlashMessage('tournament.status.denied');
        }

        return $result;
    }

    /**
     * Return if it is possible to add participant and push flash message if it is false
     *
     * @param TFTournament $TFTournament
     * @return bool
     */
    public function canAddParticipant (TFTournament $TFTournament) : bool
    {
        $isInSetup = $this->isInSetup($TFTournament);
        $isParticipantMaxed = $this->isParticipantMaxed($TFTournament);

        if (!$isInSetup) {
            $this->addFlashMessage('tournament.status.denied');
        }

        if ($isParticipantMaxed) {
            $this->addFlashMessage( 'tournament.participant.add.maxed');
        }

        return  $isInSetup && !$isParticipantMaxed;
    }

    public function canBeStarted (TFTournament $TFTournament, TFUser $user)
    {
        $isInSetup = $this->isInSetup($TFTournament);
        $hasMinParticipant = $this->hasMinParticipantRequired($TFTournament);
        $isOwner = $this->isOwner($TFTournament, $user);

        if (!$isInSetup) {
            $this->addFlashMessage( 'tournament.status.denied');
        }

        if (!$hasMinParticipant) {
            $this->addFlashMessage( 'tournament.participant.min');
        }

        if (!$isOwner) {
            $this->addFlashMessage( 'tournament.owner.denied');
        }

        return $isInSetup && $hasMinParticipant && $isOwner;
    }

    /**
     * Next methods are here to expose individual test
     */

    /**
     * @param TFTournament $TFTournament
     * @return bool
     */
    public function isParticipantMaxed (TFTournament $TFTournament) : bool
    {
        return count($TFTournament->getPlayers()) >= $TFTournament->getMaxParticipantNumber();
    }

    /**
     * @param TFTournament $TFTournament
     * @return bool
     */
    public function isInSetup (TFTournament $TFTournament) : bool
    {
        return $TFTournament->getStatus() == TournamentStatusEnum::STATUS_SETUP;
    }

    /**
     * @param TFTournament $TFTournament
     * @param TFUser $user
     * @return bool
     */
    public function isOwner (TFTournament $TFTournament, TFUser $user) : bool
    {
        return $TFTournament->getOwner() === $user;
    }

    /**
     * @param TFTournament $TFTournament
     * @param int $newMaxParticipant
     * @return bool
     */
    public function isUpdatableNBMax (TFTournament $TFTournament, int $newMaxParticipant) : bool
    {
        return count($TFTournament->getPlayers()) <= $newMaxParticipant;
    }

    /**
     * @param TFTournament $TFTournament
     * @return bool
     */
    public function hasMinParticipantRequired (TFTournament $TFTournament) : bool
    {
        return count($TFTournament->getPlayers()) >= self::MIN_PARTICIPANT_REQUIRED;
    }

    /**
     * Private methode for helping accomplish what is needed
     */

    /**
     * @param string $state
     * @param string $message
     */
    private function addFlashMessage (string $message, string $state = self::MESSAGE_TYPE_WARNING)
    {
        $this->session->getFlashBag()->add($state, $message);
    }
}