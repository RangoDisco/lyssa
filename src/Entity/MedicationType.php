<?php

namespace App\Entity;

use App\Repository\MedicationTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedicationTypeRepository::class)]
class MedicationType extends GenericEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(length: 75)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'medicationTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medication $medication = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMedication(): ?Medication
    {
        return $this->medication;
    }

    public function setMedication(?Medication $medication): static
    {
        $this->medication = $medication;

        return $this;
    }
}
