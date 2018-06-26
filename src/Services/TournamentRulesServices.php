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
    private const MESSAGE_TYPE_WARNING = 'warning';
    private const MIN_PARTICIPANT_REQUIRED = 2;
    private const MESSAGE_STATUS_DENIED = 'tournament.status.denied';

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
    public function canBeDeleted (TFTournament $TFTournament) : bool
    {
        $result = $this->isInSetup($TFTournament);

        if (!$result) {
            $this->addFlashMessage(self::MESSAGE_STATUS_DENIED);
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
            $this->addFlashMessage(self::MESSAGE_STATUS_DENIED);
        }

        if ($isParticipantMaxed) {
            $this->addFlashMessage( 'tournament.participant.add.maxed');
        }

        return  $isInSetup && !$isParticipantMaxed;
    }

    /**
     * Return if Tournament can be started or not, he need to be in status "in setup", to have more or equals
     * min participant required and the logged user need to be owner. Flash message are added to bag for each
     * false test.
     *
     * @param TFTournament $TFTournament
     * @param TFUser $user
     * @return bool
     */
    public function canBeStarted (TFTournament $TFTournament, TFUser $user) : bool
    {
        $isInSetup = $this->isInSetup($TFTournament);
        $hasMinParticipant = $this->hasMinParticipantRequired($TFTournament);
        $isOwner = $this->isOwner($TFTournament, $user);

        if (!$isInSetup) {
            $this->addFlashMessage( self::MESSAGE_STATUS_DENIED);
        }

        if (!$hasMinParticipant) {
            $this->addFlashMessage( 'tournament.status.min');
        }

        if (!$isOwner) {
            $this->addFlashMessage( 'tournament.owner.denied');
        }

        return $isInSetup && $hasMinParticipant && $isOwner;
    }

    /**
     * Check if a tournament is cancellable. It is possible if logged user is owner and
     * tournament status is "started". For each false test, a flash message is push in
     * the bag
     *
     * @param TFTournament $TFTournament
     * @param TFUser $user
     * @return bool
     */
    public function canBeCancelled (TFTournament $TFTournament, TFUser $user) : bool
    {
        $isStarted = $this->isStarted($TFTournament);
        $isOwner = $this->isOwner($TFTournament, $user);

        if (!$isStarted) {
            $this->addFlashMessage( self::MESSAGE_STATUS_DENIED);
        }

        if (!$isOwner) {
            $this->addFlashMessage( 'tournament.owner.denied');
        }

        return $isStarted && $isOwner;
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
        return \count($TFTournament->getPlayers()) >= $TFTournament->getMaxParticipantNumber();
    }

    /**
     * @param TFTournament $TFTournament
     * @return bool
     */
    public function isInSetup (TFTournament $TFTournament) : bool
    {
        return $TFTournament->getType() === TournamentStatusEnum::STATUS_SETUP;
    }

    public function isStarted (TFTournament $TFTournament) : bool
    {
        return $TFTournament->getType() === TournamentStatusEnum::STATUS_STARTED;
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
        return \count($TFTournament->getPlayers()) <= $newMaxParticipant;
    }

    /**
     * @param TFTournament $TFTournament
     * @return bool
     */
    public function hasMinParticipantRequired (TFTournament $TFTournament) : bool
    {
        $min = self::MIN_PARTICIPANT_REQUIRED > $TFTournament->getMaxParticipantNumber() ? self::MIN_PARTICIPANT_REQUIRED : $TFTournament->getMaxParticipantNumber();

        return \count($TFTournament->getPlayers()) >= $min;
    }

    /**
     * Private methode for helping accomplish what is needed
     */

    /**
     * @param string $state
     * @param string $message
     */
    private function addFlashMessage (string $message, string $state = self::MESSAGE_TYPE_WARNING) : void
    {
        $this->session->getFlashBag()->add($state, $message);
    }
}