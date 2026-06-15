<?php

declare(strict_types=1);

namespace Zitadel\Client\Test\Auth;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\Auth\PersonalAccessAuthenticator;

class PersonalAccessAuthenticatorTest extends TestCase
{
    public function testReturnsToken(): void
    {
        $authenticator = new PersonalAccessAuthenticator('https://api.example.com', 'my-secret-token');

        $this->assertSame('my-secret-token', $authenticator->getAuthToken());
    }

    /**
     * The personal access token is masked in the default debug representation.
     * Rendering through print_r() invokes __debugInfo() so the raw token never
     * leaks through var_dump() / stack traces / logs.
     */
    public function testRedactsSecret(): void
    {
        $authenticator = new PersonalAccessAuthenticator('https://api.example.com', 'pat-secret-token');

        $rendered = print_r($authenticator, true);

        $this->assertStringNotContainsString('pat-secret-token', $rendered);
        $this->assertStringContainsString('***', $rendered);
    }
}
