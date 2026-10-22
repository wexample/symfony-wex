<?php

namespace Wexample\SymfonyWex\Interface;

use Wexample\SymfonyWex\Entity\App;

/**
 * Whether what is asked of wex is committed, and signed by whom — what goes
 * with every write sent through `AgentServerClient::command()`.
 *
 * The application decides it, not the caller: a bundle writing a file into an
 * app commits it the way the rest of the installation does, without knowing
 * where that setting is kept.
 */
interface AutoCommitSettingsInterface
{
    public function autoCommit(): bool;

    /** `Name <email>` the commits made for that app are signed by. */
    public function commitAuthor(App $app): string;
}
