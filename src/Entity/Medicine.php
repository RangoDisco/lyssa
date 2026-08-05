<?php

namespace App\Entity;

use App\Enum\MedicineFormat;
use App\Enum\Source;
use App\Repository\MedicineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MedicineRepository::class)]
#[ORM\Index(name: 'idx_medicine_cis', columns: ['cis'])]
class Medicine extends GenericEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(enumType: Source::class)]
    private Source $source = Source::Community;

    #[ORM\Column(enumType: MedicineFormat::class)]
    private ?MedicineFormat $format = null;

    #[ORM\ManyToOne]
    private ?User $owner = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    private ?Media $picture = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private bool $isGeneric = false;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $cis = null;

    /**
     * @var Collection<int, MedicineSubstance>
     */
    #[ORM\OneToMany(targetEntity: MedicineSubstance::class, mappedBy: 'medicine', orphanRemoval: true)]
    private Collection $medicineSubstances;

    #[ORM\ManyToOne(targetEntity: Lab::class)]
    private ?Lab $lab = null;

    public function __construct()
    {
        $this->medicineSubstances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function setSource(Source $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

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

    public function isGeneric(): ?bool
    {
        return $this->isGeneric;
    }

    public function setIsGeneric(bool $isGeneric): static
    {
        $this->isGeneric = $isGeneric;

        return $this;
    }

    public function getCis(): ?string
    {
        return $this->cis;
    }

    public function setCis(?string $cis): static
    {
        $this->cis = $cis;

        return $this;
    }

    public function getFormat(): ?MedicineFormat
    {
        return $this->format;
    }

    public function setFormat(?MedicineFormat $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return Collection<int, MedicineSubstance>
     */
    public function getMedicineSubstances(): Collection
    {
        return $this->medicineSubstances;
    }

    public function addMedicineSubstance(MedicineSubstance $medicineSubstances): static
    {
        if (!$this->medicineSubstances->contains($medicineSubstances)) {
            $this->medicineSubstances->add($medicineSubstances);
            $medicineSubstances->setMedicine($this);
        }

        return $this;
    }

    public function removeMedicineSubstance(MedicineSubstance $medicineSubstances): static
    {
        if ($this->medicineSubstances->removeElement($medicineSubstances)) {
            // set the owning side to null (unless already changed)
            if ($medicineSubstances->getMedicine() === $this) {
                $medicineSubstances->setMedicine(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Lab>
     */
    public function getLab(): ?Lab
    {
        return $this->lab;
    }

    public function setLab(?Lab $lab): static
    {
        $this->lab = $lab;

        return $this;
    }
}
