<?php

namespace Wexample\SymfonyWex\Service;

use DateTime;
use Symfony\Component\Uid\Uuid;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Entity\Selection;
use Wexample\SymfonyWex\Repository\SelectionRepository;

/**
 * Translates a process between a plain array and its record, both ways.
 *
 * Where that array is read from and written to is not known here: a caller
 * holding a file, a payload or a fixture passes the same thing.
 */
final readonly class ProcessHydrator
{
    public const KEY_DATE_CREATED = 'date_created';
    public const KEY_OPTIONS = 'options';
    public const KEY_SELECTION_ID = 'selection_id';
    public const KEY_TITLE = 'title';
    public const KEY_TYPE = 'type';

    public function __construct(
        private SelectionRepository $selections,
    ) {
    }

    /**
     * @param array<string, mixed> $values
     */
    public function hydrate(
        Process $process,
        array $values
    ): Process {
        return $process
            ->setTitle($values[self::KEY_TITLE] ?? null)
            ->setType((string) ($values[self::KEY_TYPE] ?? ''))
            ->setSelection($this->selection($values[self::KEY_SELECTION_ID] ?? null))
            ->setOptions($this->options($values[self::KEY_OPTIONS] ?? null))
            ->setDateCreated(
                isset($values[self::KEY_DATE_CREATED])
                    ? new DateTime($values[self::KEY_DATE_CREATED])
                    : null
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function dump(Process $process): array
    {
        return [
            self::KEY_TITLE => $process->getTitle(),
            self::KEY_TYPE => $process->getType(),
            self::KEY_SELECTION_ID => $process->getSelection()?->getId()->toRfc4122(),
            self::KEY_OPTIONS => $process->getOptionValues(),
            self::KEY_DATE_CREATED => $process->getDateCreated()?->format(DATE_ATOM),
        ];
    }

    /**
     * The files the process runs on, or null: a selection can be deleted while
     * a process still names it, and the record keeps saying which one it was.
     */
    private function selection(?string $id): ?Selection
    {
        return $id && Uuid::isValid($id)
            ? $this->selections->find(Uuid::fromString($id))
            : null;
    }

    /**
     * The options as the column holds them and the form edits them: the JSON
     * text of the values the record nests, indented to be read and typed.
     *
     * No options is no text, which is what an empty field submits.
     */
    private function options(mixed $options): string
    {
        if (is_array($options)) {
            return [] === $options ? '' : rtrim(WorkdirDataReader::encode($options));
        }

        return (string) ($options ?? '');
    }
}
