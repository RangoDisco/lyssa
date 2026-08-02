<?php

namespace App\Twig\Components;

use App\Entity\MedicineSubstance;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class SubstanceMedicineListItem
{

    public MedicineSubstance $medicineSubstance;
}
