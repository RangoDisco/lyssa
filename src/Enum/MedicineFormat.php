<?php

namespace App\Enum;

enum MedicineFormat: string
{

    case Pill = 'PILL';
    case Drop = 'DROP';
    case Liquid = 'LIQUID';
    case Gel = 'GEL';
    case Injectable = 'INJECTABLE';
    case Unknown = 'UNKNOWN';

}
