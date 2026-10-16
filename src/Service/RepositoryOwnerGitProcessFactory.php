<?php

namespace Wexample\SymfonyWex\Service;

use Wexample\PhpGit\Class\GitProcessFactory;

/**
 * git run as the owner of the repository and not as this process when this
 * process is root: the board writes as root over a bind mount, and objects a
 * root git leaves in `.git` are ones the owner's own git can no longer write
 * next to.
 */
class RepositoryOwnerGitProcessFactory extends GitProcessFactory
{
    protected function command(
        string $repository,
        array $arguments,
    ): array {
        $command = parent::command($repository, $arguments);
        $owner = $this->foreignOwner($repository);

        if (null === $owner) {
            return $command;
        }

        return [
            'setpriv',
            '--reuid='.$owner,
            '--regid='.filegroup($repository.'/.git'),
            '--clear-groups',
            ...$command,
        ];
    }

    protected function env(
        string $repository,
        array $env,
    ): array {
        $env = parent::env($repository, $env);

        // Its own home is root's, which it cannot read.
        return null === $this->foreignOwner($repository)
            ? $env
            : $env + ['HOME' => sys_get_temp_dir()];
    }

    /**
     * The owner of `.git` when it is not this process — root, in the board's
     * container — and null when git can run as it is.
     */
    private function foreignOwner(string $repository): ?int
    {
        $owner = fileowner($repository.'/.git');

        return false !== $owner && 0 === posix_geteuid() && 0 !== $owner ? $owner : null;
    }
}
