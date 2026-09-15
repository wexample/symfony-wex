<?php

namespace Wexample\SymfonyWex\Message;

use Wexample\SymfonyMessenger\Message\AbstractEntityMessage;
use Wexample\SymfonyWex\Entity\ProcessRun;

/**
 * Rings for a run: the board asks for one, wex says one has moved.
 *
 * The same class serves both directions, because the kind names an entity and
 * not an intention — what the message means is said by the queue it arrived on.
 * On the way out it is routed to `process_run` and wex picks it up; on the way
 * back wex publishes on `process_run_event` and the board re-reads the record.
 *
 * The workdir travels with it because wex has no database to look the run up in:
 * knowing the app it happened in and the identity of the run is enough to find
 * the file, since where a record of a given kind lives is wex's own convention.
 */
class ProcessRunMessage extends AbstractEntityMessage
{
    public const string KEY_WORKDIR = 'workdir';

    public function __construct(
        string $id,
        private readonly string $workdir
    ) {
        parent::__construct($id);
    }

    public static function forRun(ProcessRun $run, string $workdir): self
    {
        return new self($run->getId()->toRfc4122(), $workdir);
    }

    public static function getKind(): string
    {
        return 'process_run';
    }

    public static function fromPayload(
        string $id,
        array $payload
    ): static {
        return new static($id, $payload[self::KEY_WORKDIR]);
    }

    /** The root of the app the run happened in, as both containers see it. */
    public function getWorkdir(): string
    {
        return $this->workdir;
    }

    public function getPayload(): array
    {
        return [self::KEY_WORKDIR => $this->workdir];
    }
}
