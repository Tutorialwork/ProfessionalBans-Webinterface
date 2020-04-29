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
        if($this->Time == -1){
            return "Permanent";
        }
        $timePunish = new DateTime();
        $timePunish->setTimestamp(time() - $this->Time * 60);
        $now = new DateTime();
        $diff = $now->diff($timePunish, true);
        /*
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
        */
        dump($diff);
        //$all = $diff->format('%y years %m months %a days %h hours %i minutes %s seconds');

        $years = (int) $diff->format('%y');
        $months = (int) $diff->format('%m');
        $days = (int) $diff->format('%a');
        $hours = (int) $diff->format('%h');
        $mintues = (int) $diff->format('%i');

        $str = "";
        if($days != 0){
            $str .= $days . " days, ";
        }
        if($hours != 0){
            $str .= $hours . " hours, ";
        }
        if($mintues != 0){
            $str .= $mintues . " minutes";
        }

        return $str;
    }

    /*
     * Dummy function for form
     */
    private $UnitType;

    public function getUnitType(){
        if($this->getTime() != -1){
            return 0;
        } else {
            return 3;
        }
    }

    public function getRealUnitType(){
        return $this->UnitType;
    }

    public function setUnitType($type){
        $this->UnitType = $type;
    }
}
