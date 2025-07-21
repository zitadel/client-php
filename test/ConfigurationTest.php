<?php

/** @noinspection HttpUrlsUsage */

namespace Zitadel\Client\Test;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\Auth\PersonalAccessAuthenticator;
use Zitadel\Client\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * Test constructor with valid PersonalAccessAuthenticator
     */
    public function testConstructor(): void
    {
        $authenticator = new PersonalAccessAuthenticator("http://zitadel.com", "secretmet");
        $config = new Configuration($authenticator);

        $this->assertSame(sys_get_temp_dir(), $config->getTempFolderPath());
    }

    /**
     * Test setting and getting boolean format for query string
     */
    public function testSetAndGetBooleanFormatForQueryString(): void
    {
        $authenticator = new PersonalAccessAuthenticator("http://zitadel.com", "secretmet");
        $config = new Configuration($authenticator);
        $config->setBooleanFormatForQueryString(Configuration::BOOLEAN_FORMAT_STRING);

        $this->assertEquals(Configuration::BOOLEAN_FORMAT_STRING, $config->getBooleanFormatForQueryString());
    }

    /**
     * Test user agent getter and setter
     */
    public function testUserAgent(): void
    {
        $authenticator = new PersonalAccessAuthenticator("http://zitadel.com", "secretmet");
        $config = new Configuration($authenticator);

        $this->assertMatchesRegularExpression(
            '/^zitadel-client\/\d+\.\d+\.\d+(-[a-zA-Z0-9]+(\.\d+)?)? \(lang=php; lang_version=[^;]+; os=[^;]+; arch=[^;]+\)$/',
            $config->getUserAgent()
        );
        $config->setUserAgent('CustomUserAgent/1.0');
        $this->assertEquals('CustomUserAgent/1.0', $config->getUserAgent());
    }

    /**
     * Test setting and getting debug flag
     */
    public function testDebugFlag(): void
    {
        $authenticator = new PersonalAccessAuthenticator("http://zitadel.com", "secretmet");
        $config = new Configuration($authenticator);

        $config->setDebug(true);
        $this->assertTrue($config->getDebug());
    }

    /**
     * Test setting and getting debug file
     */
    public function testDebugFile(): void
    {
        $authenticator = new PersonalAccessAuthenticator("http://zitadel.com", "secretmet");
        $config = new Configuration($authenticator);

        $config->setDebugFile('path/to/debug.log');
        $this->assertEquals('path/to/debug.log', $config->getDebugFile());
    }

    /**
     * Test getting access token
     */
    public function testGetAccessToken(): void
    {
        $authenticator = new PersonalAccessAuthenticator("http://zitadel.com", "secretmet");
        $config = new Configuration($authenticator);

        $this->assertEquals('secretmet', $config->getAccessToken());
    }

    /**
     * Test getting host from authenticator
     */
    public function testGetHost(): void
    {
        $authenticator = new PersonalAccessAuthenticator("http://zitadel.com", "secretmet");
        $config = new Configuration($authenticator);

        $this->assertEquals('http://zitadel.com', $config->getHost());
    }
}
