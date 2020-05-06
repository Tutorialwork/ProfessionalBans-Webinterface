<?php

namespace App\Entity;

use App\Controller\ProfileController;
use App\Controller\TimeController;
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

    public function getFormattedTime($day, $days, $hour, $hours, $minute, $minutes)
    {
        $time = $this->getTime() * 60;

        $timePunish = new DateTime();
        $timePunish->setTimestamp(time() - $time);
        $now = new DateTime();
        $diff = $now->diff($timePunish, true);

        dump($diff);

        $timeStr = "";
        if($diff->days != 0){
            $timeStr .= $this->buildTimeSnippet($diff->days, $day, $days);
        } else {
            if($diff->h != 0){
                $timeStr .= $this->buildTimeSnippet($diff->h, $hour, $hours);
            }
            if($diff->i != 0){
                $timeStr .= $this->buildTimeSnippet($diff->i, $minute, $minutes);
            }
        }

        return $timeStr;
    }

    private function buildTimeSnippet($number, $singular, $plural){
        if(is_numeric($number)){
            return ($number == 1) ? $number . " " . $singular . " " : $number . " " . $plural . " ";
        }
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
