<?php

namespace Wexample\SymfonyWex\Traits;

use Wexample\SymfonyHelpers\Traits\BundleClassTrait;
use Wexample\SymfonyWex\WexampleSymfonyWexBundle;

trait SymfonyWexBundleClassTrait
{
    use BundleClassTrait;

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyWexBundle::class;
    }
}
