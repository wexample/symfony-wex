<?php

namespace Wexample\SymfonyWex\Service;

use Wexample\PhpGit\Class\GitCommitter;

/**
 * Commits the paths it is given, and nothing else, in whichever repository
 * holds each of them (GitCommitter), git running as the owner of each
 * repository rather than as this process (RepositoryOwnerGitProcessFactory).
 */
class WorkdirCommitter extends GitCommitter
{
    public function __construct(
        RepositoryOwnerGitProcessFactory $processes,
    ) {
        parent::__construct($processes);
    }
}
