<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TFMatchRepository")
 */
class TFMatch
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $score;

    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\TFUser", inversedBy="matches")
     */
    private $players;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\TFTeam", inversedBy="matches")
     */
    private $teams;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TFTournament", inversedBy="matches")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tournament;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TFMatch", mappedBy="nextMatch")
     */
    private $previousMatch;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TFMatch", inversedBy="previousMatch")
     */
    private $nextMatch;

    /**
     * @ORM\Column(type="integer")
     */
    private $turn;

    /**
     * @ORM\Column(type="boolean")
     */
    private $over;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->score = [];
        $this->previousMatch = new ArrayCollection();
        $this->over = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getScore(): ?array
    {
        return $this->score;
    }

    public function setScore($index, $score): self
    {
        $this->score[$index] = $score;

        return $this;
    }

    public function setScores(?array $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection|TFUser[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(TFUser $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
        }

        return $this;
    }

    public function removePlayer(TFUser $player): self
    {
        if ($this->players->contains($player)) {
            $this->players->removeElement($player);
        }

        return $this;
    }

    /**
     * @return Collection|TFTeam[]
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(TFTeam $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
        }

        return $this;
    }

    public function removeTeam(TFTeam $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
        }

        return $this;
    }

    public function getTournament(): ?TFTournament
    {
        return $this->tournament;
    }

    public function setTournament(?TFTournament $tournament): self
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getTurn(): ?int
    {
        return $this->turn;
    }

    public function setTurn(int $turn): self
    {
        $this->turn = $turn;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPreviousMatch()
    {
        return $this->previousMatch;
    }

    /**
     * @param mixed $previousMatch
     */
    public function setPreviousMatch($previousMatch): void
    {
        $this->previousMatch = $previousMatch;
    }

    public function addPreviousMatch($previousMatch)
    {
        $this->previousMatch->add($previousMatch);
    }

    public function removePreviousMatch(TFMatch $previousMatch)
    {
        if ($this->previousMatch->contains($previousMatch)) {
            $this->previousMatch->removeElement($previousMatch);
        }
    }

    /**
     * @return TFMatch
     */
    public function getNextMatch() : ?TFMatch
    {
        return $this->nextMatch;
    }

    /**
     * @param TFMatch $nextMatch
     */
    public function setNextMatch(TFMatch $nextMatch): void
    {
        $this->nextMatch = $nextMatch;
    }

    /**
     * @return bool
     */
    public function isOver() : bool
    {
        return $this->over;
    }

    /**
     * @param bool $over
     */
    public function setOver(bool $over): void
    {
        $this->over = $over;
    }
}
