<?php

namespace Auth;

use Exception;
use Zitadel\Client\Auth\WebTokenAuthenticator;
use Zitadel\Client\Test\Auth\OAuthAuthenticatorTest;

class WebTokenAuthenticatorTest extends OAuthAuthenticatorTest
{

  /**
   * @throws Exception
   */
  public function testRefreshToken(): void
  {
    sleep(20);

    $authenticator = WebTokenAuthenticator::builder(static::$oauthHost, "1", WebTokenAuthenticatorTest::getPrivateKey())
      ->scopes(["openid", "foo"])
      ->build();

    $this->assertNotEmpty($authenticator->getAuthToken(), "Access token should not be empty");
    $token = $authenticator->refreshToken();
    $this->assertNotEmpty($token->getToken(), "Access token should not be empty");
    $this->assertFalse($token->hasExpired(), "Token expiry should be in the future");
    $this->assertEquals($token->getToken(), $authenticator->getAuthToken());
    $this->assertEquals($authenticator->getHost()->toString(), static::$oauthHost);
    $this->assertNotEquals($authenticator->refreshToken()->getToken(), $authenticator->refreshToken()->getToken());
  }

  private static function getPrivateKey(): string
  {
    $config = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
    $res = openssl_pkey_new($config);
    openssl_pkey_export($res, $key);
    return $key;
  }
}
