<?php

namespace Wexample\SymfonyWex\Service;

use Wexample\SymfonyWex\Entity\ProcessType;

/**
 * Translates a process type between a plain array and its record.
 *
 * Where that array is read from is not known here: whoever holds the list — a
 * payload from wex, a fixture — passes the same thing.
 */
final readonly class ProcessTypeHydrator
{
    public const KEY_ID = 'id';
    public const KEY_LABEL = 'label';
    public const KEY_NAME = 'name';

    /**
     * @param array<string, mixed> $values
     */
    public function hydrate(
        ProcessType $processType,
        array $values
    ): ProcessType {
        return $processType
            ->setName($values[self::KEY_NAME])
            ->setLabel($values[self::KEY_LABEL]);
    }

    /**
     * @return array<string, mixed>
     */
    public function dump(ProcessType $processType): array
    {
        return [
            self::KEY_ID => $processType->getId()->toRfc4122(),
            self::KEY_NAME => $processType->getName(),
            self::KEY_LABEL => $processType->getLabel(),
        ];
    }
}
