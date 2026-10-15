<?php

namespace Wexample\SymfonyWex\Event;

/**
 * A path of a managed app was just written, created or removed.
 *
 * Said by whatever writes into an app's repository, so that whoever has to
 * follow the writes — a commit at the end of the request — hears of all of
 * them without each writer knowing it exists.
 */
final readonly class WorkdirPathWrittenEvent
{
    public function __construct(
        public string $path,
    ) {
    }
}
