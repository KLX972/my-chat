<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait Timestamp
{
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        return $this->createdAt = new \DateTime();
    }
}
