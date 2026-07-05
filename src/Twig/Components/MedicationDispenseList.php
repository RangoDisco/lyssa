<?php

namespace App\Twig\Components;

use App\Entity\Medication;
use App\Repository\DispenseRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class MedicationDispenseList
{

    public Medication $medication;

    public function __construct(private readonly DispenseRepository $dispenseRepository)
    {
    }

    public function getDispenses(): array
    {
        return $this->dispenseRepository->findByMedication($this->medication);
    }
}
