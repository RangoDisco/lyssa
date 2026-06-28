<?php

namespace App\Entity;

use App\Enum\CaretakerAccessLevel;
use App\Repository\CaretakingAccessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CaretakingAccessRepository::class)]
class CaretakingAccess extends GenericEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: CaretakerAccessLevel::class)]
    private CaretakerAccessLevel $level = CaretakerAccessLevel::Readonly;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'careTakerAccesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $caretaker = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'patientAccesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $patient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): ?CaretakerAccessLevel
    {
        return $this->level;
    }

    public function setLevel(CaretakerAccessLevel $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getCaretaker(): ?User
    {
        return $this->caretaker;
    }

    public function setCaretaker(?User $caretaker): static
    {
        $this->caretaker = $caretaker;

        return $this;
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(?User $patient): static
    {
        $this->patient = $patient;

        return $this;
    }
}
