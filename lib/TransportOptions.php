<?php

namespace Zitadel\Client;

/**
 * Immutable transport options for configuring HTTP connections.
 */
final class TransportOptions
{
    /**
     * @param array<string, string> $defaultHeaders
     */
    public function __construct(
        public readonly array $defaultHeaders = [],
        public readonly ?string $caCertPath = null,
        public readonly bool $insecure = false,
        public readonly ?string $proxyUrl = null,
    ) {
    }

    public static function defaults(): self
    {
        return new self();
    }
}
