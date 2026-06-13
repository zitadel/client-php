<?php

declare(strict_types=1);

namespace Zitadel\Client\Auth;

use Exception;
use InvalidArgumentException;
use League\Uri\Uri;
use Zitadel\Client\TransportOptions;

/**
 * OpenId class is responsible for fetching and storing important OpenID configuration endpoints.
 * It interacts with the OpenID provider's well-known configuration endpoint and retrieves
 * the token, authorization, and userinfo endpoints.
 */
class OpenId
{
    /** @var Uri The base URL of the OpenID host. */
    private Uri $hostEndpoint;

    /** @var Uri The URL to obtain tokens. */
    private Uri $tokenEndpoint;

    /** @var Uri The URL for the authorization endpoint. */
    private Uri $authorizationEndpoint;

    /** @var Uri The URL to retrieve user information. */
    private Uri $userinfoEndpoint;

    /**
     * Constructor to initialize the OpenId instance and fetch OpenID configuration.
     *
     * This constructor accepts a hostname, fetches the OpenID configuration,
     * and stores the `token_endpoint`, `authorization_endpoint`, and `userinfo_endpoint`
     * for future use.
     *
     * @param string $hostname The hostname of the OpenID provider.
     * @param TransportOptions|null $transportOptions Optional transport options for TLS, proxy, and headers.
     * @throws InvalidArgumentException If the provided hostname is empty.
     * @throws Exception If there's an error during the HTTP request or JSON parsing.
     */
    public function __construct(
        string $hostname,
        ?TransportOptions $transportOptions = null,
    ) {
        if ($hostname === '' || $hostname === '0') {
            throw new InvalidArgumentException("Hostname cannot be empty.");
        }

        $transportOptions ??= TransportOptions::builder()->build();

        $this->hostEndpoint = $this->buildHostname($hostname);
        $config = $this->fetchOpenIdConfiguration($hostname, $transportOptions);

        $this->tokenEndpoint = Uri::new($this->requireStringField($config, 'token_endpoint'));
        $this->authorizationEndpoint = Uri::new($this->requireStringField($config, 'authorization_endpoint'));
        $this->userinfoEndpoint = Uri::new($this->requireStringField($config, 'userinfo_endpoint'));
    }

    /**
     * Extracts a required string field from the decoded OpenID configuration,
     * failing loudly if it is missing or not a string.
     *
     * @param mixed  $config The decoded configuration document.
     * @param string $field  The field name to extract.
     * @return string The field value.
     * @throws Exception If the field is absent or not a string.
     */
    private function requireStringField(mixed $config, string $field): string
    {
        if (!is_array($config) || !isset($config[$field]) || !is_string($config[$field])) {
            throw new Exception("OpenID configuration is missing a valid '$field'.");
        }
        return $config[$field];
    }

    /**
     * Builds and returns a Uri object from the provided hostname.
     * If the hostname does not include a scheme (http or https), it defaults to "https".
     *
     * @param string $hostname The hostname of the OpenID provider.
     * @return Uri A Uri object representing the full URL with the hostname.
     */
    private function buildHostname(string $hostname): Uri
    {
        if (!preg_match("/^https?:\/\//", $hostname)) {
            $hostname = "https://" . $hostname;
        }
        return Uri::new($hostname);
    }

    /**
     * Fetches the OpenID configuration from the well-known OpenID configuration endpoint.
     *
     * This method constructs the URL for the well-known endpoint, retrieves the configuration
     * in JSON format, and parses it to extract the necessary OpenID configuration fields.
     *
     * @param string $hostname The hostname of the OpenID provider.
     * @param TransportOptions $transportOptions Transport options for TLS, proxy, and headers.
     * @return mixed An associative array containing the OpenID configuration.
     * @throws Exception If the HTTP request fails, or if the JSON response is malformed.
     */
    private function fetchOpenIdConfiguration(
        string $hostname,
        TransportOptions $transportOptions,
    ): mixed {
        $wellKnownUrl = $this->buildWellKnownUrl($hostname);

        $opts = [];
        if ($transportOptions->defaultHeaders !== []) {
            $headerStr = '';
            foreach ($transportOptions->defaultHeaders as $name => $value) {
                $headerStr .= "$name: $value\r\n";
            }
            $opts['http'] = ['header' => $headerStr];
        }
        if ($transportOptions->proxy !== null) {
            $opts['http']['proxy'] = $transportOptions->proxy;
            $opts['http']['request_fulluri'] = true;
        }
        if (!$transportOptions->verifySsl) {
            $opts['ssl'] = ['verify_peer' => false, 'verify_peer_name' => false];
        } elseif ($transportOptions->caCertPath !== null) {
            $sslOpts = ['cafile' => $transportOptions->caCertPath, 'verify_peer_name' => true];
            $defaults = openssl_get_cert_locations();
            if (isset($defaults['default_cert_dir']) && is_dir($defaults['default_cert_dir'])) {
                $sslOpts['capath'] = $defaults['default_cert_dir'];
            }
            $opts['ssl'] = $sslOpts;
        }
        $context = $opts === [] ? null : stream_context_create($opts);
        $response = file_get_contents($wellKnownUrl, false, $context);

        if ($response === false) {
            throw new Exception("Failed to fetch OpenID configuration.");
        }

        $config = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to parse OpenID configuration JSON.");
        }

        return $config;
    }

    /**
     * Builds the URL to the well-known OpenID configuration endpoint.
     *
     * This method takes the provided hostname and appends "/.well-known/openid-configuration"
     * to form the URL where OpenID configuration can be fetched.
     *
     * @param string $hostname The hostname of the OpenID provider.
     * @return string The well-known URL to fetch the OpenID configuration.
     */
    private function buildWellKnownUrl(string $hostname): string
    {
        $uri = Uri::new($hostname);
        return $uri->withPath('/.well-known/openid-configuration')->toString();
    }

    /**
     * Returns the base host endpoint URL.
     *
     * This method returns the full URL for the OpenID host endpoint.
     *
     * @return Uri The host endpoint URL.
     */
    public function getHostEndpoint(): Uri
    {
        return $this->hostEndpoint;
    }

    /**
     * Returns the token endpoint URL.
     *
     * This method returns the URL for obtaining OpenID tokens.
     *
     * @return Uri The token endpoint URL.
     */
    public function getTokenEndpoint(): Uri
    {
        return $this->tokenEndpoint;
    }

    /**
     * Returns the authorization endpoint URL.
     *
     * This method returns the URL used for authorization requests.
     *
     * @return Uri The authorization endpoint URL.
     */
    public function getAuthorizationEndpoint(): Uri
    {
        return $this->authorizationEndpoint;
    }

    /**
     * Returns the userinfo endpoint URL.
     *
     * This method returns the URL for fetching user information from the OpenID provider.
     *
     * @return Uri The userinfo endpoint URL.
     */
    public function getUserinfoEndpoint(): Uri
    {
        return $this->userinfoEndpoint;
    }
}
