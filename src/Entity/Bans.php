<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="BansRepository")
 */
class Bans
{

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     */
    private $UUID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Name;

    /**
     * @ORM\Column(type="integer")
     */
    private $Banned;

    /**
     * @ORM\Column(type="integer")
     */
    private $Muted;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Reason;

    /**
     * @ORM\Column(type="integer")
     */
    private $End;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Teamuuid;

    /**
     * @ORM\Column(type="integer")
     */
    private $Bans;

    /**
     * @ORM\Column(type="integer")
     */
    private $Mutes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Firstlogin;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Lastlogin;

    /**
     * @ORM\Column(type="integer")
     */
    private $OnlineStatus;

    /**
     * @ORM\Column(type="integer")
     */
    private $OnlineTime;

    public function getUUID(): ?string
    {
        return $this->UUID;
    }

    public function setUUID(string $UUID): self
    {
        $this->UUID = $UUID;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getBanned(): ?int
    {
        return $this->Banned;
    }

    public function setBanned(int $Banned): self
    {
        $this->Banned = $Banned;

        return $this;
    }

    public function getMuted(): ?int
    {
        return $this->Muted;
    }

    public function setMuted(int $Muted): self
    {
        $this->Muted = $Muted;

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

    public function getEnd(): ?int
    {
        return $this->End;
    }

    public function setEnd(int $End): self
    {
        $this->End = $End;

        return $this;
    }

    public function getTeamUUID(): ?string
    {
        return $this->Teamuuid;
    }

    public function setTeamUUID(string $TeamUUID): self
    {
        $this->Teamuuid = $TeamUUID;

        return $this;
    }

    public function getBans(): ?int
    {
        return $this->Bans;
    }

    public function setBans(int $Bans): self
    {
        $this->Bans = $Bans;

        return $this;
    }

    public function getMutes(): ?int
    {
        return $this->Mutes;
    }

    public function setMutes(int $Mutes): self
    {
        $this->Mutes = $Mutes;

        return $this;
    }

    public function getFirstlogin(): ?string
    {
        return $this->Firstlogin;
    }

    public function setFirstlogin(string $Firstlogin): self
    {
        $this->Firstlogin = $Firstlogin;

        return $this;
    }

    public function getLastlogin(): ?string
    {
        return $this->Lastlogin;
    }

    public function setLastlogin(string $Lastlogin): self
    {
        $this->Lastlogin = $Lastlogin;

        return $this;
    }

    public function getOnlinestatus(): ?int
    {
        return $this->OnlineStatus;
    }

    public function setOnlinestatus(int $Onlinestatus): self
    {
        $this->OnlineStatus = $Onlinestatus;

        return $this;
    }

    public function getOnlinetime(): ?int
    {
        return $this->OnlineTime;
    }

    public function setOnlinetime(int $Onlinetime): self
    {
        $this->OnlineTime = $Onlinetime;

        return $this;
    }

    public function getFormattedEnd(){
        $phpTimestamp = round($this->End / 1000);
        return $phpTimestamp;
    }
}
