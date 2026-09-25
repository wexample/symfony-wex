<?php

namespace Wexample\SymfonyWex\Api\Normalizer\Entity\ProcessItem;

use ArrayObject;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;
use Wexample\SymfonyWex\Api\Dto\ProcessItemDto;
use Wexample\SymfonyWex\Entity\ProcessItem;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\ProcessItemEntityManipulatorTrait;

class DefaultProcessItemNormalizer extends AbstractEntityNormalizer
{
    use ProcessItemEntityManipulatorTrait;

    public function normalizeEntity(
        ProcessItem|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return ProcessItemDto::fromEntity($entity);
    }
}
