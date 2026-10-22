<?php

namespace Wexample\SymfonyWex\Api\Normalizer\Entity\Process;

use ArrayObject;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;
use Wexample\SymfonyWex\Api\Dto\ProcessDto;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\ProcessEntityManipulatorTrait;

class DefaultProcessNormalizer extends AbstractEntityNormalizer
{
    use ProcessEntityManipulatorTrait;

    public function normalizeEntity(
        Process|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return ProcessDto::fromEntity($entity);
    }
}
