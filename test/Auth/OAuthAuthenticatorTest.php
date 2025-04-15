<?php

namespace Zitadel\Client\Test\Auth;

use PHPUnit\Framework\TestCase;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedGenericContainer;
use Testcontainers\Wait\WaitForHttp;

/**
 * Class OAuthAuthenticatorTest
 *
 * This test class sets up a Docker container using Testcontainers to run a mock OAuth2 server.
 * It initializes the container before any tests run and tears it down after all tests are completed.
 *
 * @package YourPackageName
 */
abstract class OAuthAuthenticatorTest extends TestCase
{
  /**
   * @var string Holds the OAuth host URL constructed from the container's host and mapped port.
   */
  protected static string $oauthHost;
  /**
   * @var StartedGenericContainer|null Holds the container instance.
   */
  private static ?StartedGenericContainer $mockOAuth2Server = null;

  /**
   * Set up the Docker container before any tests are run.
   *
   * Initializes a GenericContainer with the specified image, exposes port 8080, and applies an HTTP wait strategy.
   * Constructs the OAuth host URL directly without intermediate variables.
   *
   * @return void
   */
  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();
    /** @noinspection SpellCheckingInspection */
    self::$mockOAuth2Server = (new GenericContainer("ghcr.io/navikt/mock-oauth2-server:2.1.10"))
      ->withExposedPorts(8080)
      ->start();

    (new WaitForHttp(self::$mockOAuth2Server->getMappedPort(8080)))
      ->withPath("/")
      ->withExpectedStatusCode(405)
      ->wait(self::$mockOAuth2Server);

    /** @noinspection HttpUrlsUsage */
    self::$oauthHost = "http://" . self::$mockOAuth2Server->getHost() . ":" . self::$mockOAuth2Server->getMappedPort(8080);
  }

  /**
   * Tear down the Docker container after all tests are run.
   *
   * Stops the container if it was started.
   *
   * @return void
   */
  public static function tearDownAfterClass(): void
  {
      self::$mockOAuth2Server?->stop();
    parent::tearDownAfterClass();
  }

  /**
   * Test that the OAuth host URL is set.
   *
   * Verifies that the OAuth host URL is correctly constructed and is not empty.
   *
   * @return void
   */
  public function testOAuthHostIsSet(): void
  {
    $this->assertNotEmpty(self::$oauthHost);
  }
}
