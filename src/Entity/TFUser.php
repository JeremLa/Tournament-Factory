<?php

namespace App\Entity;

use App\Entity\Abstraction\AbstractTFParticipant;
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

    public function __construct()
    {
        $this->nicknames = [];
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
}
