<?php

/** @noinspection HttpUrlsUsage */

namespace Zitadel\Client\Test;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\Auth\PersonalAccessAuthenticator;
use Zitadel\Client\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * Test user agent getter and setter
     */
    public function testUserAgent(): void
    {
        $authenticator = new PersonalAccessAuthenticator("http://zitadel.com", "secretmet");
        $config = new Configuration($authenticator);

        $this->assertMatchesRegularExpression(
            '/^zitadel-client\/\d+\.\d+\.\d+ \(lang=php; lang_version=[^;]+; os=[^;]+; arch=[^;]+\)$/',
            $config->getUserAgent()
        );
        $config->setUserAgent('CustomUserAgent/1.0');
        $this->assertEquals('CustomUserAgent/1.0', $config->getUserAgent());
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
