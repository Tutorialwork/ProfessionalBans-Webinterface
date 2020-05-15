<?php

namespace App\Entity;

use App\Repository\PrivateChatRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PrivateChatRepository::class)
 */
class Privatemessages
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
    private $Sender;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Receiver;

    /**
     * @ORM\Column(type="string", length=2500)
     */
    private $Message;

    /**
     * @ORM\Column(type="integer")
     */
    private $Status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?string
    {
        return $this->Sender;
    }

    public function setSender(string $Sender): self
    {
        $this->Sender = $Sender;

        return $this;
    }

    public function getReceiver(): ?string
    {
        return $this->Receiver;
    }

    public function setReceiver(string $Receiver): self
    {
        $this->Receiver = $Receiver;

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

    public function getStatus(): ?int
    {
        return $this->Status;
    }

    public function setStatus(int $Status): self
    {
        $this->Status = $Status;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->Date;
    }

    public function setDate(string $Date): self
    {
        $this->Date = $Date;

        return $this;
    }
}
