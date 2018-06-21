<?php

namespace App\Entity\Abstraction;

use Doctrine\ORM\Mapping as ORM;

abstract class AbstractTFParticipant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}
