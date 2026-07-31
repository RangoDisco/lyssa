<?php

namespace App\Entity;

use App\Repository\SubstanceCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubstanceCategoryRepository::class)]
class SubstanceCategory extends GenericEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(length: 75)]
    private ?string $name = null;

    /**
     * @var Collection<int, Substance>
     */
    #[ORM\ManyToMany(targetEntity: Substance::class, mappedBy: 'categories')]
    private Collection $substances;

    public function __construct()
    {
        $this->substances = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Substance>
     */
    public function getSubstances(): Collection
    {
        return $this->substances;
    }

    public function addSubstance(Substance $substance): static
    {
        if (!$this->substances->contains($substance)) {
            $this->substances->add($substance);
            $substance->addCategory($this);
        }

        return $this;
    }

    public function removeSubstance(Substance $substance): static
    {
        if ($this->substances->removeElement($substance)) {
            $substance->removeCategory($this);
        }

        return $this;
    }
}
