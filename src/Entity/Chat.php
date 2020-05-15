<?php

namespace App\Entity;

use App\Repository\ChatRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChatRepository::class)
 */
class Chat
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
    private $UUID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Server;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Message;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Senddate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUUID(): ?string
    {
        return $this->UUID;
    }

    public function setUUID(string $UUID): self
    {
        $this->UUID = $UUID;

        return $this;
    }

    public function getServer(): ?string
    {
        return $this->Server;
    }

    public function setServer(string $Server): self
    {
        $this->Server = $Server;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->Message;
    }

    public function setMessage(string $Message): self
    {
        $this->Message = $Message;

        return $this;
    }

    public function getSenddate(): ?string
    {
        return $this->Senddate;
    }

    public function setSenddate(string $Senddate): self
    {
        $this->Senddate = $Senddate;

        return $this;
    }
}
