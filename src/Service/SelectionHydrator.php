<?php

namespace Wexample\SymfonyWex\Service;

use DateTime;
use Wexample\SymfonyWex\Entity\Selection;

/**
 * Translates a selection between a plain array and its record, both ways.
 *
 * Where that array is read from and written to is not known here: a caller
 * holding a file, a payload or a fixture passes the same thing.
 */
final readonly class SelectionHydrator
{
    public const KEY_DATE_CREATED = 'date_created';
    public const KEY_PATTERNS = 'patterns';
    public const KEY_TITLE = 'title';

    /**
     * @param array<string, mixed> $values
     */
    public function hydrate(
        Selection $selection,
        array $values
    ): Selection {
        return $selection
            ->setTitle($values[self::KEY_TITLE] ?? null)
            ->setPatterns((string) ($values[self::KEY_PATTERNS] ?? ''))
            ->setDateCreated(
                isset($values[self::KEY_DATE_CREATED])
                    ? new DateTime($values[self::KEY_DATE_CREATED])
                    : null
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function dump(Selection $selection): array
    {
        return [
            self::KEY_TITLE => $selection->getTitle(),
            self::KEY_PATTERNS => $selection->getPatterns(),
            self::KEY_DATE_CREATED => $selection->getDateCreated()?->format(DATE_ATOM),
        ];
    }
}
