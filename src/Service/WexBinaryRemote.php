<?php

namespace Wexample\SymfonyWex\Service;

use Wexample\PhpRemote\Class\RemoteStatus;
use Wexample\PhpRemote\Interface\RemoteInterface;
use Wexample\PhpWex\Common\WexClient;

/**
 * The wex binary as a remote: available when `wex --version` runs, which
 * reads the version and touches nothing.
 */
final readonly class WexBinaryRemote implements RemoteInterface
{
    public function __construct(
        private WexClient $client,
    ) {
    }

    public function getKey(): string
    {
        return 'wex_binary';
    }

    public function getLabel(): string
    {
        return 'wex binary';
    }

    public function checkStatus(): RemoteStatus
    {
        $result = $this->client->execute(['--version']);

        return $result->isSuccessful()
            ? RemoteStatus::up('wex '.strtok(trim($result->stdout), "\n"))
            : RemoteStatus::down(trim($result->stderr) ?: 'Exit code '.$result->returnCode.'.');
    }
}
