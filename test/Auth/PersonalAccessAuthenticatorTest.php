<?php

namespace Zitadel\Client\Test\Auth;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\Auth\PersonalAccessAuthenticator;

class PersonalAccessAuthenticatorTest extends TestCase
{
  public function testReturnsToken(): void
  {
    $host = 'https://api.example.com';
    $token = 'my-secret-token';

    $authenticator = new PersonalAccessAuthenticator($host, $token);

    $this->assertSame($token, $authenticator->getAuthToken());
  }
}
