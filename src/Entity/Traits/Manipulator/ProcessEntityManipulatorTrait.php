<?php

namespace Wexample\SymfonyWex\Entity\Traits\Manipulator;

use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;
use Wexample\SymfonyWex\Entity\Process;

trait ProcessEntityManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return Process::class;
    }
}
