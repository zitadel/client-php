<?php

declare(strict_types=1);

namespace Zitadel\Client\Test;

use Zitadel\Client\DefaultApiClient;
use Zitadel\Client\TransportOptions;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Test-only subclass that overrides the protected {@see
 * DefaultApiClient::createHttpClient()} transport seam to return a caller-
 * supplied stub client. The production constructor no longer accepts an HTTP
 * client argument (it would leak the Symfony transport type onto the public
 * API), so unit tests inject their MockHttpClient by overriding the factory
 * via this subclass — mirroring how the Ruby SDK stubs its private
 * build_connection.
 */
final class StubbedDefaultApiClient extends DefaultApiClient
{
    public function __construct(
        private readonly HttpClientInterface $stubClient,
        ?TransportOptions $transportOptions = null,
    ) {
        parent::__construct($transportOptions);
    }

    #[\Override]
    protected function createHttpClient(): HttpClientInterface
    {
        return $this->stubClient;
    }
}
