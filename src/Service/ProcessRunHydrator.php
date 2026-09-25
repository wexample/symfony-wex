<?php

namespace Wexample\SymfonyWex\Service;

use DateTime;
use Symfony\Component\Uid\Uuid;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Entity\ProcessRun;
use Wexample\SymfonyWex\Repository\ProcessRepository;

/**
 * Translates a run between a plain array and its record, both ways.
 *
 * The keys are the contract the worker writes against: they are read here by
 * the board and written there by wex, so renaming one is renaming it in two
 * languages.
 */
final readonly class ProcessRunHydrator
{
    /**
     * What the run is asked to do, in place of the action its process declares:
     * a check process fixed from one of its runs. Written by the board, read by
     * wex; absent, the process's own.
     */
    public const KEY_ACTION = 'action';
    /**
     * Whether wex commits what the run changed once it is done. Written by the
     * board, read by wex; after the run, what was committed.
     */
    public const KEY_COMMIT = 'commit';
    public const KEY_CURRENT = 'current';
    public const KEY_DATA = 'data';
    public const KEY_DATE_CREATED = 'date_created';
    public const KEY_DATE_ENDED = 'date_ended';
    public const KEY_DATE_STARTED = 'date_started';
    public const KEY_ITEMS_DONE = 'items_done';
    /**
     * What the run goes through, in place of what its selection holds: paths
     * relative to the app. Written by the board, read by wex; absent, the
     * selection.
     */
    public const KEY_ITEMS = 'items';
    public const KEY_ITEMS_TOTAL = 'items_total';
    public const KEY_PROCESS_ID = 'process_id';
    public const KEY_STATE = 'state';

    public function __construct(
        private ProcessRepository $processes,
    ) {
    }

    /**
     * @param array<string, mixed> $values
     */
    public function hydrate(
        ProcessRun $run,
        array $values
    ): ProcessRun {
        return $run
            ->setProcess($this->process($values[self::KEY_PROCESS_ID] ?? null))
            ->setState((string) ($values[self::KEY_STATE] ?? ProcessRun::STATE_PENDING))
            ->setItemsTotal(isset($values[self::KEY_ITEMS_TOTAL]) ? (int) $values[self::KEY_ITEMS_TOTAL] : null)
            ->setItemsDone((int) ($values[self::KEY_ITEMS_DONE] ?? 0))
            ->setCurrent(isset($values[self::KEY_CURRENT]) ? (string) $values[self::KEY_CURRENT] : null)
            ->setData($this->data($values[self::KEY_DATA] ?? null))
            ->setDateCreated($this->date($values[self::KEY_DATE_CREATED] ?? null))
            ->setDateStarted($this->date($values[self::KEY_DATE_STARTED] ?? null))
            ->setDateEnded($this->date($values[self::KEY_DATE_ENDED] ?? null));
    }

    /**
     * @return array<string, mixed>
     */
    public function dump(ProcessRun $run): array
    {
        return [
            self::KEY_PROCESS_ID => $run->getProcess()?->getId()->toRfc4122(),
            self::KEY_STATE => $run->getState(),
            self::KEY_ITEMS_TOTAL => $run->getItemsTotal(),
            self::KEY_ITEMS_DONE => $run->getItemsDone(),
            self::KEY_CURRENT => $run->getCurrent(),
            self::KEY_DATA => $run->getDataValues(),
            self::KEY_DATE_CREATED => $run->getDateCreated()?->format(DATE_ATOM),
            self::KEY_DATE_STARTED => $run->getDateStarted()?->format(DATE_ATOM),
            self::KEY_DATE_ENDED => $run->getDateEnded()?->format(DATE_ATOM),
        ];
    }

    /**
     * What was run, or null: a process can be deleted while its runs are still
     * there, and the record keeps saying which one it was.
     */
    private function process(?string $id): ?Process
    {
        return $id && Uuid::isValid($id)
            ? $this->processes->find(Uuid::fromString($id))
            : null;
    }

    /**
     * The result as the column holds it: the JSON text of the values.
     *
     * Nested in the record since records are JSON; a string is still taken as
     * it is, which is what a record written before that looks like.
     */
    private function data(mixed $data): string
    {
        if (is_array($data)) {
            return [] === $data ? '' : rtrim(WorkdirDataReader::encode($data));
        }

        return (string) ($data ?? '');
    }

    private function date(?string $value): ?DateTime
    {
        return $value ? new DateTime($value) : null;
    }
}
