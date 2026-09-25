<?php

namespace Wexample\SymfonyWex\Service;

use Wexample\SymfonyWex\Entity\App;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Entity\ProcessRun;
use Wexample\SymfonyWex\Repository\ProcessRunRepository;

/**
 * What a run produced that can be handed back: the files it wrote.
 *
 * Read from the key a type lists them under, `written` — a PDF export fills it,
 * and a type that writes nothing leaves it out. The key is the only thing the
 * board and a type agree on here: what the files are is the type's business,
 * and serving them is the board's.
 */
final readonly class ProcessRunArtifacts
{
    /** Where a type lists the files it wrote, relative to the app. */
    public const string KEY_WRITTEN = 'written';

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
     * The files the run wrote, relative to the app, in the order it listed them.
     *
     * Read from the record and not from the row: the row catches up once the
     * board has heard the run end, and a page asking for the file the moment
     * the run is done would otherwise be told there is none.
     *
     * @return string[]
     */
    public function paths(ProcessRun $run): array
    {
        $record = is_file($run->getPath()) ? $this->reader->read($run->getPath()) : [];

        return array_values(array_filter(
            (array) ($record[ProcessRunHydrator::KEY_DATA][self::KEY_WRITTEN] ?? []),
            is_string(...)
        ));
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
