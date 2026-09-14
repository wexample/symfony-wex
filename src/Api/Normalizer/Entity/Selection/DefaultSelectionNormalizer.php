<?php

namespace Wexample\SymfonyWex\Api\Normalizer\Entity\Selection;

use ArrayObject;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;
use Wexample\SymfonyWex\Api\Dto\SelectionDto;
use Wexample\SymfonyWex\Entity\Selection;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\SelectionEntityManipulatorTrait;

class DefaultSelectionNormalizer extends AbstractEntityNormalizer
{
    use SelectionEntityManipulatorTrait;

    public function normalizeEntity(
        Selection|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return SelectionDto::fromEntity($entity);
    }
}
