<?php

declare(strict_types=1);

namespace Wexample\SymfonyWex\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wexample\SymfonyWex\Service\AgentServerClient;

class AgentServerClientTest extends TestCase
{
    /**
     * @param list<string> $chunks the body as the wire cuts it
     *
     * @return list<array<string, mixed>>
     */
    private function events(array $chunks): array
    {
        $client = new AgentServerClient(
            new MockHttpClient(new MockResponse($chunks)),
            new NullLogger(),
            'http://agent.test',
            'token',
        );

        return iterator_to_array($client->sendToSession('/app', 'session', 'Hello'), false);
    }

    public function testLinesCutAcrossChunksAreJoined(): void
    {
        $events = $this->events([
            "{\"type\":\"session\"}\n{\"type\":\"te",
            "xt\",\"text\":\"Hi\"}\n\n",
            "{\"type\":\"result\"}\n",
        ]);

        $this->assertSame(
            [['type' => 'session'], ['type' => 'text', 'text' => 'Hi'], ['type' => 'result']],
            $events
        );
    }

    public function testTheLastEventWithoutALineBreakIsStillRead(): void
    {
        $events = $this->events([
            "{\"type\":\"session\"}\n",
            '{"type":"result"}',
        ]);

        $this->assertSame([['type' => 'session'], ['type' => 'result']], $events);
    }

    public function testATrailingBlankRemainderMakesNoEvent(): void
    {
        $this->assertSame([['type' => 'result']], $this->events(["{\"type\":\"result\"}\n  "]));
    }
}
