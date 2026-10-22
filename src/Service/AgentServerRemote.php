<?php

namespace Wexample\SymfonyWex\Service;

use Wexample\PhpRemote\Class\RemoteStatus;
use Wexample\PhpRemote\Interface\RemoteInterface;

/**
 * The wex agent server as a remote: reachable when it answers the login
 * status, the one request that runs no turn and costs nothing.
 */
final readonly class AgentServerRemote implements RemoteInterface
{
    public function __construct(
        private AgentServerClient $client,
        private ?string $url,
    ) {
    }

    public function getKey(): string
    {
        return 'wex_agent_server';
    }

    public function getLabel(): string
    {
        return 'wex agent server';
    }

    public function checkStatus(): RemoteStatus
    {
        if (null === $this->url || '' === $this->url) {
            return RemoteStatus::unconfigured('Missing: wexample_symfony_wex.agent_server.url.');
        }

        // Throws with what the server said when it refuses; the registry reports it.
        $this->client->loginStatus();

        return RemoteStatus::up();
    }
}
