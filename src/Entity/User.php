<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(
 *     fields="email",
 *     errorPath="email",
 *     message="Cet email est déjà utilisé."
 * )
 *
 * @UniqueEntity(
 *     fields="username",
 *     errorPath="username",
 *     message="Cet identifiant est déjà utilisé."
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, EquatableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $salt;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\TFUser", cascade={"persist"})
     */
    private $tfUser;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->tfUser = new TFUser();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(?string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return TFUser
     */
    public function getTfUser() : TFUser
    {
        return $this->tfUser;
    }

    /**
     * @param mixed $tfUser
     */
    public function setTfUser($tfUser): void
    {
        $this->tfUser = $tfUser;
    }


    public function eraseCredentials()
    {
    }


    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        $return = true;

        if ($this->password !== $user->getPassword()) {
            $return = false;
        }

        if ($this->salt !== $user->getSalt()) {
            $return = false;
        }

        if ($this->username !== $user->getUsername()) {
            $return = false;
        }

        return $return;
    }
}
