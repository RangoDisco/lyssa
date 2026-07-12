<?php

namespace App\Twig\Components;

use App\Entity\Dispense;
use App\Entity\Variant;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class MedicationVariantListItem
{

    public Variant $variant;
}
