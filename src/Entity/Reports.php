<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReportsRepository")
 */
class Reports
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
    private $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Reporter;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Team;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Reason;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Log;

    /**
     * @ORM\Column(type="integer")
     */
    private $Status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $CreatedAt;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getReporter(): ?string
    {
        return $this->Reporter;
    }

    public function setReporter(string $Reporter): self
    {
        $this->Reporter = $Reporter;

        return $this;
    }

    public function getTeam(): ?string
    {
        return $this->Team;
    }

    public function setTeam(string $Team): self
    {
        $this->Team = $Team;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->Reason;
    }

    public function setReason(string $Reason): self
    {
        $this->Reason = $Reason;

        return $this;
    }

    public function getLog(): ?string
    {
        return $this->Log;
    }

    public function setLog(?string $Log): self
    {
        $this->Log = $Log;

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

    public function getCreatedAt(): ?string
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(string $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }
}
