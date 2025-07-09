<?php

namespace Zitadel\Client;

use Zitadel\Client\Auth\Authenticator;

class Configuration
{
    /**
     * User agent of the HTTP request.
     *
     * @var string
     */
    protected string $userAgent;

    /**
     * Initializes a new instance of the API client.
     *
     * @param Authenticator $authenticator The authenticator for signing requests.
     * @param int $timeout The total request timeout in seconds.
     * @param int $connectTimeout The connection timeout in seconds.
     */
    public function __construct(
        protected Authenticator $authenticator,
        private readonly int $timeout = 30,
        private readonly int $connectTimeout = 5
    ) {
        $this->userAgent = self::myUserAgent();
    }

    public static function myUserAgent(): string
    {
        return sprintf('zitadel-client/%s (lang=php; lang_version=%s; os=%s; arch=%s)', Version::VERSION, PHP_VERSION, strtolower(PHP_OS_FAMILY), strtolower(php_uname('m')));
    }

    /**
     * Gets the access token for OAuth
     *
     * @return string Access token for OAuth
     */
    public function getAccessToken(): string
    {
        return $this->authenticator->getAuthToken();
    }

    /**
     * Gets the boolean format for query string.
     *
     * @return string Boolean format for query string
     */
    public function getBooleanFormatForQueryString(): string
    {
        return 'int';
    }

    /**
     * Gets the host
     *
     * @return string Host
     */
    public function getHost(): string
    {
        return $this->authenticator->getHost()->toString();
    }

    /**
     * Gets the user agent of the api client
     *
     * @return string user agent
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * Sets the user agent of the api client
     *
     * @param string $userAgent the user agent of the api client
     *
     * @return $this
     * @noinspection PhpUnused*@throws \InvalidArgumentException
     */
    public function setUserAgent(string $userAgent): Configuration
    {

        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * Gets the debug flag
     *
     * @return bool
     */
    public function getDebug(): bool
    {
        return false;
    }

    /**
     * Gets the connection timeout.
     *
     * Specifies the number of seconds to wait while trying to connect to a
     * server.
     *
     * @return int The connection timeout in seconds.
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * Gets the total request timeout.
     *
     * Specifies the maximum number of seconds that the entire request is
     * allowed to take.
     *
     * @return int The total timeout in seconds.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
