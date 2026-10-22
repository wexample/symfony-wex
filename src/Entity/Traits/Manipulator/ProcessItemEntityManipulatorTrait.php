<?php

namespace Wexample\SymfonyWex\Entity\Traits\Manipulator;

use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;
use Wexample\SymfonyWex\Entity\ProcessItem;

trait ProcessItemEntityManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return ProcessItem::class;
    }
}
