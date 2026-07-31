<?php

namespace App\Entity;

use     App\Enum\Source;
use App\Repository\SubstanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubstanceRepository::class)]
#[ORM\Index(name: 'idx_substance_code', columns: ['code'])]
class Substance extends GenericEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $code = null;

    /**
     * @var Collection<int, SubstanceCategory>
     */
    #[ORM\ManyToMany(targetEntity: SubstanceCategory::class, inversedBy: 'substances')]
    private Collection $categories;

    #[ORM\ManyToOne]
    private ?User $owner = null;

    #[ORM\Column(enumType: Source::class)]
    private ?Source $source = Source::Community;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, SubstanceCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(SubstanceCategory $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(SubstanceCategory $category): static
    {
        $this->categories->removeElement($category);

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

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function setSource(Source $source): static
    {
        $this->source = $source;

        return $this;
    }
}
