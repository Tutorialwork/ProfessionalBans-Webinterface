<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LogRepository")
 */
class Log
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
    private $Byuuid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Action;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Note;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Date;

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

    public function getByUUID(): ?string
    {
        return $this->Byuuid;
    }

    public function setByUUID(string $ByUUID): self
    {
        $this->Byuuid = $ByUUID;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->Action;
    }

    public function setAction(string $Action): self
    {
        $this->Action = $Action;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->Note;
    }

    public function setNote(?string $Note): self
    {
        $this->Note = $Note;

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
