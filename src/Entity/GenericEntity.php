<?php

namespace App\Entity;

use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

abstract class GenericEntity
{
    use SoftDeleteableEntity, TimestampableEntity;

}
