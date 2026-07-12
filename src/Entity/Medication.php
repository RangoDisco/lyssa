<?php

namespace App\Entity;

use App\Enum\MedicationSource;
use App\Repository\MedicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MedicationRepository::class)]
class Medication extends GenericEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    /**
     * @var Collection<int, Variant>
     */
    #[ORM\OneToMany(targetEntity: Variant::class, mappedBy: 'medication', orphanRemoval: true)]
    private Collection $variants;

    #[ORM\Column(enumType: MedicationSource::class)]
    private ?MedicationSource $source = MedicationSource::Community;

    #[ORM\ManyToOne]
    private ?User $owner = null;

    /**
     * @var Collection<int, MedicationType>
     */
    #[ORM\OneToMany(targetEntity: MedicationType::class, mappedBy: 'medication', orphanRemoval: true)]
    private Collection $medicationTypes;

    #[ORM\ManyToOne(cascade: ['persist'])]
    private ?Media $picture = null;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
        $this->medicationTypes = new ArrayCollection();
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

    /**
     * @return Collection<int, Variant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(Variant $variant): static
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            $variant->setMedication($this);
        }

        return $this;
    }

    public function removeVariant(Variant $variant): static
    {
        if ($this->variants->removeElement($variant)) {
            // set the owning side to null (unless already changed)
            if ($variant->getMedication() === $this) {
                $variant->setMedication(null);
            }
        }

        return $this;
    }

    public function getSource(): ?MedicationSource
    {
        return $this->source;
    }

    public function setSource(MedicationSource $source): static
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

    /**
     * @return Collection<int, MedicationType>
     */
    public function getMedicationTypes(): Collection
    {
        return $this->medicationTypes;
    }

    public function addMedicationType(MedicationType $medicationTypes): static
    {
        if (!$this->medicationTypes->contains($medicationTypes)) {
            $this->medicationTypes->add($medicationTypes);
            $type->setMedication($this);
        }

        return $this;
    }

    public function removeMedicationType(MedicationType $medicationTypes): static
    {
        if ($this->medicationTypes->removeElement($medicationTypes)) {
            // set the owning side to null (unless already changed)
            if ($medicationTypes->getMedication() === $this) {
                $medicationTypes->setMedication(null);
            }
        }

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
}
