<?php

namespace Wexample\SymfonyWex\Service;

use Wexample\SymfonyWex\Entity\App;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Entity\ProcessRun;
use Wexample\SymfonyWex\Repository\ProcessRunRepository;

/**
 * What a run produced that can be handed back: the files it wrote.
 *
 * Read from `artifacts`, which the runner writes from what a type declares
 * through its context — `{type, path, label}`, the same shape for every type.
 * A run from before that lists bare paths under `written`, read the same way.
 * What the files are is the type's business, and serving them is the board's.
 */
final readonly class ProcessRunArtifacts
{
    /** What the runner lists a run's artifacts under. */
    public const string KEY_ARTIFACTS = 'artifacts';

    /** Where a type listed the files it wrote before artifacts: bare paths. */
    public const string KEY_WRITTEN = 'written';

    public const string TYPE_FILE = 'file';

    /**
     * How many runs back a document's latest artifact is looked for. A process
     * run on one file at a time piles its runs up; an export older than that is
     * one to make again rather than to hunt for.
     */
    private const int LOOKBACK = 50;

    public function __construct(
        private ProcessRunRepository $runs,
        private WorkdirDataReader $reader,
    ) {
    }

    /**
     * The files the run produced, in the order it listed them, as
     * `{type, path, label}` with the path relative to the app.
     *
     * Read from the record and not from the row: the row catches up once the
     * board has heard the run end, and a page asking for the file the moment
     * the run is done would otherwise be told there is none.
     *
     * @return array<int, array{type: string, path: string, label: string|null}>
     */
    public function all(ProcessRun $run): array
    {
        $record = is_file($run->getPath()) ? $this->reader->read($run->getPath()) : [];
        $data = (array) ($record[ProcessRunHydrator::KEY_DATA] ?? []);

        if (is_array($data[self::KEY_ARTIFACTS] ?? null)) {
            return array_values(array_filter(array_map(
                static fn (mixed $artifact): ?array => is_array($artifact) && is_string($artifact['path'] ?? null)
                    ? [
                        'type' => (string) ($artifact['type'] ?? self::TYPE_FILE),
                        'path' => $artifact['path'],
                        'label' => isset($artifact['label']) ? (string) $artifact['label'] : null,
                    ]
                    : null,
                $data[self::KEY_ARTIFACTS]
            )));
        }

        return array_map(
            static fn (string $path): array => ['type' => self::TYPE_FILE, 'path' => $path, 'label' => null],
            array_values(array_filter((array) ($data[self::KEY_WRITTEN] ?? []), is_string(...)))
        );
    }

    /**
     * The paths of the files the run produced, relative to the app.
     *
     * @return string[]
     */
    public function paths(ProcessRun $run): array
    {
        return array_column(
            array_filter($this->all($run), static fn (array $artifact): bool => self::TYPE_FILE === $artifact['type']),
            'path'
        );
    }

    /**
     * One of them as an absolute path, or null when it is not there any more or
     * would lead outside the app.
     */
    public function file(App $app, ProcessRun $run, int $index): ?string
    {
        $relative = $this->paths($run)[$index] ?? null;

        if (null === $relative) {
            return null;
        }

        $root = realpath($app->getPath());
        $path = realpath($app->getPath().'/'.$relative);

        return $root && $path && is_file($path) && str_starts_with($path, $root.'/')
            ? $path
            : null;
    }

    /**
     * The latest run of the process on that one item whose first artifact is
     * still there and not older than the item itself.
     *
     * An export made before the document was last written is not the document
     * any more, so it is not offered: the caller makes a new one instead.
     */
    public function latestFresh(App $app, Process $process, string $item): ?ProcessRun
    {
        $source = $app->getPath().'/'.$item;

        if (! is_file($source)) {
            return null;
        }

        foreach (array_slice($this->runs->findByProcess($process), 0, self::LOOKBACK) as $run) {
            $record = is_file($run->getPath()) ? $this->reader->read($run->getPath()) : [];

            if (ProcessRun::STATE_COMPLETE !== ($record[ProcessRunHydrator::KEY_STATE] ?? null)
                || [$item] !== ($record[ProcessRunHydrator::KEY_ITEMS] ?? null)) {
                continue;
            }

            $artifact = $this->file($app, $run, 0);

            return $artifact && filemtime($artifact) >= filemtime($source) ? $run : null;
        }

        return null;
    }
}
