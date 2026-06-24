<?php

namespace App\Entity;

use App\Repository\PrescriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: PrescriptionRepository::class)]
class Prescription extends GenericEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Doctor $doctor = null;

    #[ORM\ManyToOne(inversedBy: 'prescriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $patient = null;

    /**
     * @var Collection<int, Dispense>
     */
    #[ORM\OneToMany(targetEntity: Dispense::class, mappedBy: 'prescription', orphanRemoval: true)]
    private Collection $dispenses;

    public function __construct()
    {
        $this->dispenses = new ArrayCollection();
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

    public function getDoctor(): ?Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(?Doctor $doctor): static
    {
        $this->doctor = $doctor;

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

    /**
     * @return Collection<int, Dispense>
     */
    public function getDispenses(): Collection
    {
        return $this->dispenses;
    }

    public function addDispense(Dispense $dispense): static
    {
        if (!$this->dispenses->contains($dispense)) {
            $this->dispenses->add($dispense);
            $dispense->setPrescription($this);
        }

        return $this;
    }

    public function removeDispense(Dispense $dispense): static
    {
        if ($this->dispenses->removeElement($dispense)) {
            // set the owning side to null (unless already changed)
            if ($dispense->getPrescription() === $this) {
                $dispense->setPrescription(null);
            }
        }

        return $this;
    }
}
