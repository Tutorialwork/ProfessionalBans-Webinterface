<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReasonRepository")
 */
class Reasons
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
    private $Reason;

    /**
     * @ORM\Column(type="integer")
     */
    private $Time;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Type;

    /**
     * @ORM\Column(type="datetime")
     */
    private $Added_at;

    /**
     * @ORM\Column(type="integer")
     */
    private $Bans;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Perms;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTime(): ?int
    {
        return $this->Time;
    }

    public function setTime(int $Time): self
    {
        $this->Time = $Time;

        return $this;
    }

    public function getType(): ?bool
    {
        return $this->Type;
    }

    public function setType(bool $Type): self
    {
        $this->Type = $Type;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeInterface
    {
        return $this->Added_at;
    }

    public function setAddedAt(\DateTimeInterface $Added_at): self
    {
        $this->Added_at = $Added_at;

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

    public function getPerms(): ?string
    {
        return $this->Perms;
    }

    public function setPerms(?string $Perms): self
    {
        $this->Perms = $Perms;

        return $this;
    }

    public function getFormattedTime()
    {
        $timePunish = new DateTime();
        $timePunish->setTimestamp(time() - $this->Time * 60);
        $now = new DateTime();
        $diff = $now->diff($timePunish, true);
        if($diff->d != 0 && $diff->h != 0 && $diff->i != 0){
            return $diff->d . " days, " . $diff->h . " hours and " . $diff->i ." minutes";
        } else if($diff->d == 0 && $diff->h != 0 && $diff->i != 0){
            return $diff->h . " hours and " . $diff->i ." minutes";
        } else if($diff->d == 0 && $diff->h == 0 && $diff->i != 0){
            return $diff->i ." minutes";
        } else if($diff->d == 0 && $diff->h != 0 && $diff->i == 0){
            return $diff->h ." hours";
        } else if($diff->d != 0 && $diff->h == 0 && $diff->i == 0){
            return $diff->d ." days";
        }
    }
}
