<?php

namespace App\Entity;

use App\Enum\MedicationFormat;
use App\Repository\VariantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: VariantRepository::class)]
class Variant extends GenericEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?int $dosage = null;

    #[ORM\ManyToOne(inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medication $medication = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    private ?Media $picture = null;

    #[ORM\Column(enumType: MedicationFormat::class)]
    private ?MedicationFormat $format = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDosage(): ?int
    {
        return $this->dosage;
    }

    public function setDosage(int $dosage): static
    {
        $this->dosage = $dosage;

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

    public function getPicture(): ?Media
    {
        return $this->picture;
    }

    public function setPicture(?Media $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getFormat(): ?MedicationFormat
    {
        return $this->format;
    }

    public function setFormat(MedicationFormat $format): static
    {
        $this->format = $format;

        return $this;
    }
}
