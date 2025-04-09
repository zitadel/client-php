<?php

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
}
