<?php

namespace Wexample\SymfonyWex\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Runs a turn on the wex agent server, and reads what comes back as it comes.
 *
 * The server answers a turn with a stream: one JSON object per line, the first
 * one written before the model has produced anything and the last one saying
 * whether it worked. Reading the whole body at once would give the same content
 * minutes later, which is the whole of what this class is careful about.
 */
final readonly class AgentServerClient
{
    public const string EVENT_ERROR = 'error';

    public const string EVENT_RESULT = 'result';

    public const string EVENT_SESSION = 'session';

    public const string KEY_MESSAGE = 'message';

    public const string KEY_TYPE = 'type';

    public const string PATH_MESSAGE = '/agent/message';

    /**
     * Seconds of silence tolerated between two events. A turn thinks before it
     * says anything, and thinks again between two lines; only a server that has
     * stopped talking altogether should end up here.
     */
    private const int TIMEOUT_IDLE = 300;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private ?string $url,
        private ?string $token,
    ) {
    }

    /**
     * Continues a conversation, and gives back each event as the server writes it.
     *
     * @param string $appPath where the app holding the conversation is mounted,
     *                        which the server sees at that same path
     *
     * @return iterable<array<string, mixed>>
     */
    public function sendToSession(
        string $appPath,
        string $sessionId,
        string $prompt,
    ): iterable {
        if (null === $this->url) {
            throw new RuntimeException('No agent server is configured: see wexample_symfony_wex.agent_server.');
        }

        $response = $this->httpClient->request(
            Request::METHOD_POST,
            $this->url.self::PATH_MESSAGE,
            [
                'auth_bearer' => $this->token,
                'json' => [
                    // Which app the turn is run on, since one server answers for
                    // all the ones it was given. Named by its path and not by an
                    // identity: the path is what both sides see. Always named,
                    // even when it is the app the server was started on — a
                    // server that disagrees with us then refuses the turn instead
                    // of running it somewhere else.
                    'app' => $appPath,
                    'session_id' => $sessionId,
                    'prompt' => $prompt,
                ],
                'timeout' => self::TIMEOUT_IDLE,
                // A turn lasts as long as the model does, and nothing here knows
                // how long that is.
                'max_duration' => 0,
            ]
        );

        // Whatever is decided before the stream opens is a status, and this is the
        // only moment one can be read: once the stream is open, a failure is an
        // event in a 200.
        if (Response::HTTP_OK !== $status = $response->getStatusCode()) {
            throw new RuntimeException(sprintf(
                'The agent server refused the turn (%d): %s',
                $status,
                $response->getContent(false)
            ));
        }

        $buffer = '';

        foreach ($this->httpClient->stream($response) as $chunk) {
            $buffer .= $chunk->getContent();

            // A chunk is a piece of the wire and not a line: the end of one is
            // usually cut in two, and waits here for the rest of itself.
            while (false !== $break = strpos($buffer, "\n")) {
                $line = trim(substr($buffer, 0, $break));
                $buffer = substr($buffer, $break + 1);

                if ('' !== $line) {
                    $event = json_decode($line, true, flags: JSON_THROW_ON_ERROR);

                    $this->log($event);

                    yield $event;
                }
            }
        }
    }

    /**
     * Leaves a trace of the turn on the `agent` channel.
     *
     * A turn says a hundred things and three of them matter: which conversation
     * it opened, and how it ended. The rest is the model thinking out loud, kept
     * at debug so that reading a failure does not mean reading a transcript.
     *
     * @param array<string, mixed> $event
     */
    private function log(array $event): void
    {
        $type = $event[self::KEY_TYPE] ?? null;

        $level = match ($type) {
            self::EVENT_ERROR => LogLevel::ERROR,
            self::EVENT_RESULT, self::EVENT_SESSION => LogLevel::INFO,
            default => LogLevel::DEBUG,
        };

        $this->logger->log(
            $level,
            sprintf('Agent turn event "%s".', $type),
            $event
        );
    }
}
