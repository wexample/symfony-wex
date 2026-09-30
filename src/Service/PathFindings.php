<?php

namespace Wexample\SymfonyWex\Service;

use Wexample\SymfonyWex\Entity\App;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Repository\ProcessItemRepository;
use Wexample\SymfonyWex\Repository\ProcessRepository;

/**
 * What the processes of an app hold against each of its files, by path.
 *
 * For whoever draws the files of an app — a tree, a listing — and wants to show
 * at a glance which ones a process found at fault. Read from where the files
 * stand, like the findings of the app are, and weighed by the severity each
 * process declares: a file is as serious as the most serious process against it.
 */
final readonly class PathFindings
{
    public function __construct(
        private ProcessRepository $processes,
        private ProcessItemRepository $items,
    ) {
    }

    /**
     * @return array<string, array{severity: string, processes: string[]}> path,
     *         relative to the app, to its most serious severity and the titles
     *         of the processes against it
     */
    public function read(App $app): array
    {
        $findings = [];

        foreach ($this->items->findFaultsByProcesses($this->processes->findByApp($app)) as $item) {
            $process = $item->getProcess();
            $finding = $findings[$item->getPath()] ?? ['severity' => $process->getSeverity(), 'processes' => []];

            $finding['severity'] = self::heaviest($finding['severity'], $process->getSeverity());
            $finding['processes'][] = (string) $process->getTitle();

            $findings[$item->getPath()] = $finding;
        }

        return $findings;
    }

    /** The more serious of two severities, so a whole can wear the worst of its parts. */
    public static function heaviest(string $left, string $right): string
    {
        $weight = array_flip(Process::SEVERITIES_BY_WEIGHT);

        return ($weight[$right] ?? PHP_INT_MAX) < ($weight[$left] ?? PHP_INT_MAX) ? $right : $left;
    }
}
