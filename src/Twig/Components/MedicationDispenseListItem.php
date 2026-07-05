<?php

namespace App\Twig\Components;

use App\Entity\Dispense;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class MedicationDispenseListItem
{

    public Dispense $dispense;
    public function getIcon(): string
    {
        if ($this->dispense->getDoneAt() !== null) {
            return 'lucide:check';
        }

        return 'lucide:clock';
    }

    public function getBackgroundColor(): string {
        if($this->dispense->getDoneAt() !== null) {
            return 'primary/30';
        }

        return 'secondary/40';
    }

    public function getIconColor(): string {
        if($this->dispense->getDoneAt() !== null) {
            return 'primary';
        }

        return 'secondary';
    }
}
