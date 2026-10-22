<?php

namespace Wexample\SymfonyWex\Api\Normalizer\Entity\ProcessRun;

use ArrayObject;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;
use Wexample\SymfonyLive\Interface\LiveEntityNormalizerInterface;
use Wexample\SymfonyWex\Api\Dto\ProcessRunDto;
use Wexample\SymfonyWex\Entity\ProcessRun;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\ProcessRunEntityManipulatorTrait;

/**
 * Also what a run is published as: a page watching one wants the same fields it
 * was rendered with, so there is no second shape to keep in step.
 */
class DefaultProcessRunNormalizer extends AbstractEntityNormalizer implements LiveEntityNormalizerInterface
{
    use ProcessRunEntityManipulatorTrait;

    public function normalizeEntity(
        ProcessRun|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return ProcessRunDto::fromEntity($entity);
    }
}
