<?php

namespace Zitadel\Client;

use Zitadel\Client\Auth\Authenticator;
use Zitadel\Client\Auth\NoAuthAuthenticator;

class Configuration
{
    public static function myUserAgent(): string
    {
        return sprintf('zitadel-client/%s (lang=php; lang_version=%s; os=%s; arch=%s)', Version::VERSION, PHP_VERSION, strtolower(PHP_OS_FAMILY), strtolower(php_uname('m')));
    }

    public const BOOLEAN_FORMAT_INT = 'int';
    public const BOOLEAN_FORMAT_STRING = 'string';

    /**
     * @var Configuration
     */
    private static Configuration $defaultConfiguration;

    /**
     * Boolean format for query string
     *
     * @var string
     */
    protected string $booleanFormatForQueryString = self::BOOLEAN_FORMAT_INT;

    /**
     * User agent of the HTTP request, set to "OpenAPI-Generator/{version}/PHP" by default
     *
     * @var string
     */
    protected string $userAgent;

    /**
     * Debug switch (default set to false)
     *
     * @var bool
     */
    protected bool $debug = false;

    /**
     * Debug file location (log to STDOUT by default)
     *
     * @var string
     */
    protected string $debugFile = 'php://output';

    /**
     * Debug file location (log to STDOUT by default)
     *
     * @var string
     */
    protected string $tempFolderPath;

    /**
     * Constructor
     */
    public function __construct(protected Authenticator $authenticator)
    {
        $this->tempFolderPath = sys_get_temp_dir();
        $this->userAgent = self::myUserAgent();
    }

    /**
     * Gets the default configuration instance
     *
     * @return Configuration
     */
    public static function getDefaultConfiguration(): Configuration
    {
        // @phpstan-ignore-next-line
        if (self::$defaultConfiguration === null) {
            self::$defaultConfiguration = new Configuration(new NoAuthAuthenticator());
        }

        return self::$defaultConfiguration;
    }

    /**
     * Sets the default configuration instance
     *
     * @param Configuration $config An instance of the Configuration Object
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function setDefaultConfiguration(Configuration $config): void
    {
        self::$defaultConfiguration = $config;
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
     * Gets boolean format for query string.
     *
     * @return string Boolean format for query string
     */
    public function getBooleanFormatForQueryString(): string
    {
        return $this->booleanFormatForQueryString;
    }

    /**
     * Sets boolean format for query string.
     *
     * @param string $booleanFormat Boolean format for query string
     *
     * @return $this
     * @noinspection PhpUnused
     */
    public function setBooleanFormatForQueryString(string $booleanFormat): Configuration
    {
        $this->booleanFormatForQueryString = $booleanFormat;

        return $this;
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
        return $this->debug;
    }

    /**
     * Sets debug flag
     *
     * @param bool $debug Debug flag
     *
     * @return $this
     * @noinspection PhpUnused
     */
    public function setDebug(bool $debug): Configuration
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Gets the debug file
     *
     * @return string
     */
    public function getDebugFile(): string
    {
        return $this->debugFile;
    }

    /**
     * Sets the debug file
     *
     * @param string $debugFile Debug file
     *
     * @return $this
     * @noinspection PhpUnused
     */
    public function setDebugFile(string $debugFile): Configuration
    {
        $this->debugFile = $debugFile;
        return $this;
    }

    /**
     * Gets the temp folder path
     *
     * @return string Temp folder path
     */
    public function getTempFolderPath(): string
    {
        return $this->tempFolderPath;
    }

    /**
     * Sets the temp folder path
     *
     * @param string $tempFolderPath Temp folder path
     *
     * @return $this
     * @noinspection PhpUnused
     */
    public function setTempFolderPath(string $tempFolderPath): Configuration
    {
        $this->tempFolderPath = $tempFolderPath;
        return $this;
    }

}
