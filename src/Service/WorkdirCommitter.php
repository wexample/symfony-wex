<?php

namespace Wexample\SymfonyWex\Service;

use Symfony\Component\Process\Process;

/**
 * Commits the paths it is given, and nothing else, in whichever repository
 * holds each of them.
 *
 * Each path is staged as it stands on disk — changed, new or gone —, a path
 * git ignores or sees no change on is dropped, and what else is changed in the
 * repository stays out of the commit: the work of whoever else edits it is not
 * ours to sign. The counterpart of wex's `git_commit_paths`, for what is
 * written from here.
 *
 * git runs as the owner of the repository and not as this process: the board
 * writes as root over a bind mount, and objects a root git leaves in `.git`
 * are ones the owner's own git can no longer write next to.
 */
class WorkdirCommitter
{
    /**
     * @param list<string>                         $paths  absolute, in one repository or several
     * @param (callable(string): ?string)|null     $author `Name <email>` for a repository, given its
     *                                                     root; the repository's own when null
     *
     * @return array<string, string> the commit made in each repository, by its root
     */
    public function commit(
        array $paths,
        string $message,
        ?callable $author = null,
    ): array {
        $commits = [];

        foreach ($this->byRepository($paths) as $repository => $relative) {
            $sha = $this->commitIn($repository, $relative, $message, $author ? $author($repository) : null);

            if (null !== $sha) {
                $commits[$repository] = $sha;
            }
        }

        return $commits;
    }

    /**
     * @param list<string> $paths
     */
    private function commitIn(
        string $repository,
        array $paths,
        string $message,
        ?string $author,
    ): ?string {
        $paths = array_values(array_diff($paths, $this->ignored($repository, $paths)));

        if (! $paths) {
            return null;
        }

        $this->git($repository, ['add', '-A', '--', ...$paths]);

        // Both sides of a rename, not only where the file went: committing the
        // new path alone would leave the removal of the old one staged.
        $staged = array_values(array_filter(explode("\n", $this->git(
            $repository,
            ['diff', '--cached', '--name-only', '--no-renames', '--relative', '--', ...$paths]
        ))));

        if (! $staged) {
            return null;
        }

        $this->git($repository, ['commit', '-m', $message, '--', ...$staged], $this->identity($author));

        return trim($this->git($repository, ['rev-parse', 'HEAD']));
    }

    /**
     * @param list<string> $paths
     *
     * @return list<string> those of the paths git ignores
     */
    private function ignored(string $repository, array $paths): array
    {
        $process = $this->process($repository, ['check-ignore', '--stdin']);
        $process->setInput(implode("\n", $paths)."\n");
        // Exits 1 when none is ignored, which is not a failure.
        $process->run();

        return array_values(array_filter(explode("\n", $process->getOutput())));
    }

    /**
     * @param list<string> $paths
     *
     * @return array<string, list<string>> the paths relative to their repository, by its root
     */
    private function byRepository(array $paths): array
    {
        $groups = [];

        foreach (array_unique($paths) as $path) {
            $repository = $this->repositoryOf($path);

            if (null !== $repository) {
                $groups[$repository][] = ltrim(substr($path, strlen($repository)), '/');
            }
        }

        return $groups;
    }

    /**
     * The repository a path belongs to, found by climbing to the nearest
     * `.git` — from the path itself when it is gone, since a removal is a
     * write too.
     */
    private function repositoryOf(string $path): ?string
    {
        $directory = is_dir($path) ? $path : dirname($path);

        while ('/' !== $directory && '.' !== $directory) {
            if (file_exists($directory.'/.git')) {
                return $directory;
            }

            $directory = dirname($directory);
        }

        return null;
    }

    /**
     * @return array<string, string> the identity git reads from the environment
     */
    private function identity(?string $author): array
    {
        if (null === $author) {
            return [];
        }

        [$name, $email] = array_map('trim', explode('<', $author, 2)) + [1 => ''];
        $email = rtrim($email, '>');

        return [
            'GIT_AUTHOR_NAME' => $name,
            'GIT_AUTHOR_EMAIL' => $email,
            'GIT_COMMITTER_NAME' => $name,
            'GIT_COMMITTER_EMAIL' => $email,
        ];
    }

    /**
     * @param list<string>          $arguments
     * @param array<string, string> $env
     */
    private function git(string $repository, array $arguments, array $env = []): string
    {
        $process = $this->process($repository, $arguments, $env);
        $process->mustRun();

        return $process->getOutput();
    }

    /**
     * @param list<string>          $arguments
     * @param array<string, string> $env
     */
    private function process(string $repository, array $arguments, array $env = []): Process
    {
        $command = ['git', '-c', 'safe.directory='.$repository, '-C', $repository, ...$arguments];

        // As the owner of `.git` when this process is someone else — root, in
        // the board's container.
        $owner = fileowner($repository.'/.git');
        if (false !== $owner && 0 === posix_geteuid() && 0 !== $owner) {
            $command = [
                'setpriv',
                '--reuid='.$owner,
                '--regid='.filegroup($repository.'/.git'),
                '--clear-groups',
                ...$command,
            ];
            // Its own home is root's, which it cannot read.
            $env += ['HOME' => sys_get_temp_dir()];
        }

        return new Process($command, null, $env);
    }
}
