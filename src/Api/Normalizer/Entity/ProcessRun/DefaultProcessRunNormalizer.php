<?php

namespace Wexample\SymfonyWex\Api\Normalizer\Entity\ProcessRun;

use ArrayObject;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;
use Wexample\SymfonyWex\Api\Dto\ProcessRunDto;
use Wexample\SymfonyWex\Entity\ProcessRun;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\ProcessRunEntityManipulatorTrait;

class DefaultProcessRunNormalizer extends AbstractEntityNormalizer
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
