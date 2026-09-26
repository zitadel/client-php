<?php

declare(strict_types=1);

namespace Zitadel\Client\Test\Auth;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zitadel\Client\Auth\PersonalAccessTokenAuthenticator;

class PersonalAccessAuthenticatorTest extends TestCase
{
    public function testReturnsToken(): void
    {
        $authenticator = new PersonalAccessTokenAuthenticator('api.example.com', 'my-secret-token');

        $this->assertSame(['Authorization' => 'Bearer my-secret-token'], $authenticator->getAuthHeaders());
        $this->assertSame('https://api.example.com', $authenticator->getHost());
    }

    /**
     * An empty token or an invalid host is an InvalidArgumentException.
     */
    public function testRejectsBadArguments(): void
    {
        foreach ([['https://api.example.com', ''], ['ftp://api.example.com', 'my-secret-token']] as [$host, $token]) {
            try {
                new PersonalAccessTokenAuthenticator($host, $token);
                $this->fail('Expected InvalidArgumentException');
            } catch (InvalidArgumentException $e) {
                $this->assertSame(InvalidArgumentException::class, $e::class);
            }
        }
    }

    /**
     * The personal access token is masked in the default debug representation.
     * Rendering through print_r() invokes __debugInfo() so the raw token never
     * leaks through var_dump() / stack traces / logs.
     */
    public function testRedactsSecret(): void
    {
        $authenticator = new PersonalAccessTokenAuthenticator('https://api.example.com', 'pat-secret-token');

        $rendered = print_r($authenticator, true);

        $this->assertStringNotContainsString('pat-secret-token', $rendered);
        $this->assertStringContainsString('***', $rendered);
    }
}
