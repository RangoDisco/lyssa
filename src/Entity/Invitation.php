<?php

namespace App\Entity;

use App\Enum\CaretakerAccessLevel;
use App\Repository\InvitationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InvitationRepository::class)]
class Invitation extends GenericEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $patient = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $token = null;

    #[ORM\Column(enumType: CaretakerAccessLevel::class)]
    private ?CaretakerAccessLevel $accessLevel = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getToken(): ?Uuid
    {
        return $this->token;
    }

    public function setToken(Uuid $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getAccessLevel(): ?CaretakerAccessLevel
    {
        return $this->accessLevel;
    }

    public function setAccessLevel(CaretakerAccessLevel $accessLevel): static
    {
        $this->accessLevel = $accessLevel;

        return $this;
    }
}
