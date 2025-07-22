<?php

/** @noinspection HttpUrlsUsage */

namespace Zitadel\Client\Test;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\Auth\NoAuthAuthenticator;
use Zitadel\Client\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * OAuth host for testing.
     *
     * @var string
     */
    private static string $oauthHost = 'http://zitadel.com';

    /**
     * Test user agent getter and setter.
     *
     * @return void
     */
    public function testUserAgent(): void
    {
        $authenticator = new NoAuthAuthenticator(self::$oauthHost, "test-token");
        $config = new Configuration($authenticator);

        $this->assertMatchesRegularExpression(
            '/^zitadel-client\/\d+\.\d+\.\d+(-[a-zA-Z0-9]+(\.\d+)?)? \(lang=php; lang_version=[^;]+; os=[^;]+; arch=[^;]+\)$/',
            $config->getUserAgent()
        );
    }

    /**
     * Test getting access token.
     *
     * @return void
     */
    public function testGetAccessToken(): void
    {
        $authenticator = new NoAuthAuthenticator(self::$oauthHost, "test-token");
        $config = new Configuration($authenticator);

        $this->assertEquals('test-token', $config->getAccessToken());
    }

    /**
     * Test getting host from authenticator.
     *
     * @return void
     */
    public function testGetHost(): void
    {
        $authenticator = new NoAuthAuthenticator(self::$oauthHost, "test-token");
        $config = new Configuration($authenticator);

        $this->assertEquals(self::$oauthHost, $config->getHost());
    }

    /**
     * Test connection timeout.
     *
     * @return void
     */
    public function testGetConnectTimeout(): void
    {
        $authenticator = new NoAuthAuthenticator(self::$oauthHost, "test-token");
        $config = new Configuration($authenticator);

        $this->assertEquals(5, $config->getConnectTimeout());
    }

    /**
     * Test total timeout.
     *
     * @return void
     */
    public function testGetTimeout(): void
    {
        $authenticator = new NoAuthAuthenticator(self::$oauthHost, "test-token");
        $config = new Configuration($authenticator);

        $this->assertEquals(30, $config->getTimeout());
    }
}
