<?php

namespace Wexample\SymfonyWex\Entity\Traits\Manipulator;

use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;
use Wexample\SymfonyWex\Entity\ProcessType;

trait ProcessTypeEntityManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return ProcessType::class;
    }
}
