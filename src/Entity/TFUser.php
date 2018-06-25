<?php

namespace App\Entity;

use App\Entity\Abstraction\AbstractTFParticipant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TFUserRepository")
 */
class TFUser extends AbstractTFParticipant
{

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(name="nicknames", type="array", nullable=true)
     */
    private $nicknames;

    /**
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @var Collection $tournaments
     * @ORM\ManyToMany(targetEntity="App\Entity\TFTournament", inversedBy="players")
     */
    protected $tournaments;

    /**
     * @var Collection $ownedtournaments
     * @ORM\OneToMany(targetEntity="App\Entity\TFTournament", mappedBy="owner")
     */
    private $ownedtournaments;

    public function __construct()
    {
        parent::__construct();
        $this->nicknames = [];
        $this->ownedtournaments = new ArrayCollection;
        $this->tournaments = new ArrayCollection;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return array
     */
    public function getNicknames()
    {
        return $this->nicknames;
    }

    /**
     * @param array $nicknames
     */
    public function setNicknames(array $nicknames): void
    {
        $this->nicknames = $nicknames;
    }

    /**
     * @param string $nickname
     */
    public function addNickname(string $nickname) : void
    {
        $this->nicknames[] = $nickname;
    }

    /**
     * @param string $nickname
     */
    public function removeNickname(string $nickname) : void
    {
            $index = array_search($nickname, $this->nicknames);
            unset($this->nicknames[$index]);
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
    }

    /**
     * @return Collection
     */
    public function getOwnedtournaments(): Collection
    {
        return $this->ownedtournaments;
    }

    /**
     * @param TFTournament $TFTournament
     */
    public function addOwnedtournaments(TFTournament $TFTournament): void
    {
        $this->ownedtournaments->add($TFTournament);
    }

    /**
     * @param TFTournament $TFTournament
     */
    public function removeOwnedtournaments(TFTournament $TFTournament): void
    {
        $this->ownedtournaments->removeElement($TFTournament);
    }

    /**
     * @return Collection
     */
    public function getTournaments(): Collection
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
    public function removeTournament(TFTournament $TFTournament): void
    {
        $this->tournaments->removeElement($TFTournament);
    }


    public function __toString()
    {
        return $this->getId() . '/' . $this->getEmail();
    }

}
