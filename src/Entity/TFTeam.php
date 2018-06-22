<?php
/**
 * Created by PhpStorm.
 * User: AHermes
 * Date: 21/06/2018
 * Time: 15:47
 */

namespace App\Entity;


use App\Entity\Abstraction\AbstractTFParticipant;
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

}