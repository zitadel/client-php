<?php

declare(strict_types=1);

namespace Zitadel\Client\Test\Auth;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\Auth\NoAuthAuthenticator;

class NoAuthAuthenticatorTest extends TestCase
{
    public function testReturnsEmptyHeadersAndDefaultHost(): void
    {
        $authenticator = new NoAuthAuthenticator();

        $this->assertSame([], $authenticator->getAuthHeaders());
        $this->assertSame('http://localhost', $authenticator->getHost());
    }
}
