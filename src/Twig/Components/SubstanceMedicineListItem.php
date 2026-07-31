<?php

namespace App\Twig\Components;

use App\Entity\Medicine;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class SubstanceMedicineListItem
{

    public Medicine $medicine;
}
