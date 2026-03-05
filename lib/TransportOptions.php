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

    /**
     * Builds Guzzle HTTP client options from these transport options.
     *
     * @return array<string, mixed>
     */
    public function toGuzzleOptions(): array
    {
        $opts = [];
        if ($this->insecure) {
            $opts['verify'] = false;
        } elseif ($this->caCertPath !== null) {
            $opts['verify'] = $this->caCertPath;
            $defaults = openssl_get_cert_locations();
            if (isset($defaults['default_cert_dir']) && is_dir($defaults['default_cert_dir'])) {
                $opts['curl'] = [CURLOPT_CAPATH => $defaults['default_cert_dir']];
            }
        }
        if ($this->proxyUrl !== null) {
            $opts['proxy'] = $this->proxyUrl;
        }
        if (!empty($this->defaultHeaders)) {
            $opts['headers'] = $this->defaultHeaders;
        }
        return $opts;
    }
}
