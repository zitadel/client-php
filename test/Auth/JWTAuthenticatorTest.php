<?php

namespace Auth;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Zitadel\Client\Auth\JWTAuthenticator;
use Zitadel\Client\Test\Auth\OAuthAuthenticatorTest;

class JWTAuthenticatorTest extends OAuthAuthenticatorTest
{

  private static function getPrivateKey(): string {
      $config = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
      $res = openssl_pkey_new($config);
      openssl_pkey_export($res, $key);
      return $key;
  }

  /**
   * @throws GuzzleException
   * @throws Exception
   */
  public function testRefreshToken(): void
  {
    sleep(20);

    $authenticator = JWTAuthenticator::builder(static::$oauthHost, "1", JWTAuthenticatorTest::getPrivateKey())
      ->scopes(["openid", "foo"])
      ->build();

    $token = $authenticator->refreshToken();
    $this->assertNotEmpty($token->getToken(), "Access token should not be empty");
    $this->assertFalse($token->hasExpired(), "Token expiry should be in the future");
    $this->assertEquals($token->getToken(), $authenticator->getAuthToken());
  }
}
