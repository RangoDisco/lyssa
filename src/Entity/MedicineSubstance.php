<?php

namespace App\Entity;

use App\Enum\DosageUnitEnum;
use App\Repository\MedicineSubstanceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedicineSubstanceRepository::class)]
class MedicineSubstance extends GenericEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'medicineSubstances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medicine $medicine = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Substance $substance = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(enumType: DosageUnitEnum::class)]
    private ?DosageUnitEnum $unit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedicine(): ?Medicine
    {
        return $this->medicine;
    }

    public function setMedicine(?Medicine $medicine): static
    {
        $this->medicine = $medicine;

        return $this;
    }

    public function getSubstance(): ?Substance
    {
        return $this->substance;
    }

    public function setSubstance(?Substance $substance): static
    {
        $this->substance = $substance;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getUnit(): ?DosageUnitEnum
    {
        return $this->unit;
    }

    public function setUnit(DosageUnitEnum $unit): static
    {
        $this->unit = $unit;

        return $this;
    }
}
