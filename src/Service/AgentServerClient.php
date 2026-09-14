<?php

namespace Wexample\SymfonyWex\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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

    public const string KEY_ERROR = 'error';

    public const string KEY_MESSAGE = 'message';

    public const string KEY_RECORDS = 'records';

    public const string KEY_TYPE = 'type';

    public const string PATH_DATA = '/data/';

    public const string PATH_LOGIN_COMPLETE = '/auth/claude/complete';

    public const string PATH_LOGIN_START = '/auth/claude/start';

    public const string PATH_LOGIN_STATUS = '/auth/claude/status';

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
        $response = $this->httpClient->request(
            Request::METHOD_POST,
            $this->url().self::PATH_MESSAGE,
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
     * The records of a kind wex owns, in the order it holds them.
     *
     * Same shape as a `.wex/data` directory read from disk — an identity and a
     * few fields — so what comes back goes to the same projector, and a kind
     * added on the server is not a pipe added here.
     *
     * @return array<int, array<string, mixed>>
     */
    public function data(string $kind): array
    {
        return $this->call(Request::METHOD_GET, self::PATH_DATA.$kind)[self::KEY_RECORDS];
    }

    /**
     * Opens a login on the account the turns are charged to.
     *
     * What comes back is the address to approve it at, and the identity of the
     * login it belongs to. The secret that closes it stays on the server, so
     * nothing but that identity has to be kept here until the operator returns.
     *
     * @return array{flow_id: string, authorize_url: string, expires_in: int}
     */
    public function loginStart(): array
    {
        return $this->call(Request::METHOD_POST, self::PATH_LOGIN_START, []);
    }

    /**
     * Closes a login with the code the approval page answered.
     *
     * @return array<string, mixed> the connection as it stands once it is done
     */
    public function loginComplete(
        string $flowId,
        string $code,
    ): array {
        return $this->call(Request::METHOD_POST, self::PATH_LOGIN_COMPLETE, [
            'flow_id' => $flowId,
            'code' => $code,
        ]);
    }

    /**
     * What the server knows of the connection, which is never the token itself.
     *
     * @return array<string, mixed>
     */
    public function loginStatus(): array
    {
        return $this->call(Request::METHOD_GET, self::PATH_LOGIN_STATUS);
    }

    /**
     * @param array<string, mixed>|null $body
     *
     * @return array<string, mixed>
     */
    private function call(
        string $method,
        string $path,
        ?array $body = null,
    ): array {
        $options = ['auth_bearer' => $this->token];

        if (null !== $body) {
            $options['json'] = $body;
        }

        $response = $this->httpClient->request($method, $this->url().$path, $options);

        // The server says a request to redo apart from a refusal that redoing
        // will not change, by the status it answers. Both reach the caller as
        // what it said, which is the only thing worth showing to whoever is
        // waiting in front of it.
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new RuntimeException($this->refusal($response));
        }

        return $response->toArray();
    }

    private function refusal(ResponseInterface $response): string
    {
        $body = json_decode($response->getContent(false), true);

        return is_array($body) && isset($body[self::KEY_ERROR])
            ? (string) $body[self::KEY_ERROR]
            : sprintf('The agent server answered %d.', $response->getStatusCode());
    }

    private function url(): string
    {
        return $this->url
            ?? throw new RuntimeException('No agent server is configured: see wexample_symfony_wex.agent_server.');
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
