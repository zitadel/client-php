<?php

declare(strict_types=1);

namespace Zitadel\Client\Test;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A minimal PSR-18 client that replays a queue of canned responses and records
 * every {@see RequestInterface} it is handed, so tests can assert what the
 * Psr18ApiClient put on the wire. Mirrors the role of the Symfony MockHttpClient
 * in the DefaultApiClient unit tests, exercising the SAME shared orchestration
 * ({@see Zitadel\Client\AbstractApiClient}) through the PSR-18 transport.
 */
final class StubPsr18Client implements ClientInterface
{
    /** @var list<ResponseInterface> */
    private array $responses;

    /** @var list<RequestInterface> */
    public array $requests = [];

    public function __construct(ResponseInterface ...$responses)
    {
        $this->responses = array_values($responses);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        if ($this->responses === []) {
            throw new class ('no stubbed response remaining') extends \RuntimeException implements
                ClientExceptionInterface {
            };
        }

        return array_shift($this->responses);
    }
}
