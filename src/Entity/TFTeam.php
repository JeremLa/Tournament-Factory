<?php
/**
 * Created by PhpStorm.
 * User: AHermes
 * Date: 21/06/2018
 * Time: 15:47
 */

namespace App\Entity;


use App\Entity\Abstraction\AbstractTFParticipant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class TFTeam extends AbstractTFParticipant
{

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var ArrayCollection $tournaments
     * @ORM\ManyToMany(targetEntity="App\Entity\TFTournament", inversedBy="teams")
     */
    protected $tournaments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\TFMatch", mappedBy="teams")
     */
    private $matches;

    public function __construct()
    {
        $this->tournaments = new ArrayCollection;
        $this->matches = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getTournaments(): ArrayCollection
    {
        return $this->tournaments;
    }

    /**
     * @param TFTournament $TFTournament
     */
    public function addTournaments(TFTournament $TFTournament): void
    {
        $this->tournaments->add($TFTournament);
    }

    /**
     * @param TFTournament $TFTournament
     */
    public function removeTournaments(TFTournament $TFTournament): void
    {
        $this->tournaments->removeElement($TFTournament);
    }

    /**
     * @return Collection|TFMatch[]
     */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    public function addMatch(TFMatch $match): self
    {
        if (!$this->matches->contains($match)) {
            $this->matches[] = $match;
            $match->addTeam($this);
        }

        return $this;
    }

    public function removeMatch(TFMatch $match): self
    {
        if ($this->matches->contains($match)) {
            $this->matches->removeElement($match);
            $match->removeTeam($this);
        }

        return $this;
    }

}

