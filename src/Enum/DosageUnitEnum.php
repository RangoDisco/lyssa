<?php

namespace App\Enum;

enum DosageUnitEnum: string
{
    case Milligram = 'mg';
    case Gram = 'g';
    case MicroGram = 'μg';
    case InternationalUnit = 'ui';
    case UCEIP = 'u.ceip';
    case Milliliter = 'ml';
    case DH = 'dh';
    case Unit = 'unites';
    case Micrometer = 'mu';
}
