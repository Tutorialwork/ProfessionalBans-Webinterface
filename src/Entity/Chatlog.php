<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChatlogRepository")
 */
class Chatlog
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
    private $Logid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Server;

    /**
     * @ORM\Column(type="string", length=2500)
     */
    private $Message;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Senddate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $CreatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $CreatorUuid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogid(): ?string
    {
        return $this->Logid;
    }

    public function setLogid(string $Logid): self
    {
        $this->Logid = $Logid;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

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

    public function getCreatedAt(): ?string
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(string $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getCreatorUuid(): ?string
    {
        return $this->CreatorUuid;
    }

    public function setCreatorUuid(string $CreatorUuid): self
    {
        $this->CreatorUuid = $CreatorUuid;

        return $this;
    }
}
