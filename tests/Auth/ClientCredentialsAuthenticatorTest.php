<?php

declare(strict_types=1);

namespace Zitadel\Client\Test\Auth;

use ArrayObject;
use Exception;
use InvalidArgumentException;
use LogicException;
use Zitadel\Client\ApiClient;
use Zitadel\Client\ApiHttpResponse;
use Zitadel\Client\Auth\ClientCredentialsAuthenticator;
use Zitadel\Client\Auth\OpenId;
use Zitadel\Client\DefaultApiClient;
use Zitadel\Client\Errors\ApiException;
use Zitadel\Client\Errors\InternalServerErrorException;
use Zitadel\Client\Errors\NetworkException;
use Zitadel\Client\Errors\NotFoundException;
use Zitadel\Client\Errors\OAuth2ServerException;
use Zitadel\Client\Errors\OAuth2TokenException;
use Zitadel\Client\Errors\SerializationException;
use Zitadel\Client\Errors\ZitadelException;
use Zitadel\Client\TransportOptions;

/**
 * Tests for the ClientCredentialsAuthenticator and the OAuth contract it
 * shares with every OAuth authenticator: host validation, OpenID discovery
 * failures and token endpoint failures.
 *
 * No contract test reaches a real host. HTTP-level cases use an in-memory
 * {@see ApiClient} that answers with canned responses; the transport case uses
 * the real {@see DefaultApiClient} against a local port nothing listens on.
 */
class ClientCredentialsAuthenticatorTest extends OAuthAuthenticatorTestCase
{
    private const string HOST = 'https://zitadel.example.com';

    private const string DISCOVERY =
        '{"issuer":"https://zitadel.example.com","token_endpoint":"https://zitadel.example.com/oauth/v2/token"}';

    /**
     * @throws Exception
     */
    public function testRefreshToken(): void
    {
        $authenticator = $this->withApiClient(
            ClientCredentialsAuthenticator::builder(static::$oauthHost, "dummy-client", "dummy-secret")
                ->build()
        );

        $this->assertNotEmpty($authenticator->getAuthToken(), "Access token should not be empty");
        $token = $authenticator->refreshToken();
        $this->assertNotEmpty($token, "Access token should not be empty");
        $this->assertEquals($token, $authenticator->getAuthToken());
        $this->assertEquals($authenticator->getHost(), static::$oauthHost);
        $this->assertNotEquals($authenticator->refreshToken(), $authenticator->refreshToken());
    }

    /**
     * The client secret is masked in the default debug representation while the
     * client id stays visible. Rendering through print_r() invokes __debugInfo()
     * so the raw secret never leaks through var_dump() / stack traces / logs.
     */
    public function testRedactsSecret(): void
    {
        $authenticator = new ClientCredentialsAuthenticator(
            new OpenId('https://api.example.com'),
            'visible-client-id',
            'super-secret-value'
        );

        $rendered = print_r($authenticator, true);

        $this->assertStringNotContainsString('super-secret-value', $rendered);
        $this->assertStringContainsString('***', $rendered);
        $this->assertStringContainsString('visible-client-id', $rendered);
    }

    public function testMintsAndCachesToken(): void
    {
        /** @var ArrayObject<int, string> $bodies */
        $bodies = new ArrayObject();
        $authenticator = $this->stubbed($this->stub(200, self::DISCOVERY, 200, '{"access_token":"t0k3n","expires_in":3600}', $bodies));

        $this->assertSame('t0k3n', $authenticator->getAuthToken());
        $this->assertSame(['Authorization' => 'Bearer t0k3n'], $authenticator->getAuthHeaders());
        $this->assertCount(1, $bodies);
        $this->assertStringStartsWith('grant_type=client_credentials&scope=openid', (string) $bodies[0]);
    }

    public function testRejectsEmptyCredentials(): void
    {
        $this->assertThrowsExactly(
            InvalidArgumentException::class,
            fn (): \Zitadel\Client\Auth\ClientCredentialsAuthenticatorBuilder => ClientCredentialsAuthenticator::builder(self::HOST, '', 'client-secret')
        );
        $this->assertThrowsExactly(
            InvalidArgumentException::class,
            fn (): \Zitadel\Client\Auth\ClientCredentialsAuthenticatorBuilder => ClientCredentialsAuthenticator::builder(self::HOST, 'client-1', ' ')
        );
    }

    public function testRejectsBadHost(): void
    {
        foreach (['', 'ftp://example.com', 'https://'] as $host) {
            $error = $this->assertThrowsExactly(
                InvalidArgumentException::class,
                fn (): \Zitadel\Client\Auth\ClientCredentialsAuthenticatorBuilder => ClientCredentialsAuthenticator::builder($host, 'client-1', 'client-secret')
            );
        }
    }

    public function testRequiresApiClient(): void
    {
        $authenticator = ClientCredentialsAuthenticator::builder(self::HOST, 'client-1', 'client-secret')->build();

        $this->assertThrowsExactly(LogicException::class, fn (): string => $authenticator->getAuthToken());
    }

    public function testDiscoveryUnreachable(): void
    {
        $authenticator = $this->stubbed(new DefaultApiClient(TransportOptions::builder()->build()), 'http://127.0.0.1:1');

        $error = $this->assertThrowsExactly(NetworkException::class, fn (): string => $authenticator->getAuthToken());
        $this->assertInstanceOf(ApiException::class, $error);
        $this->assertSame(0, $error->getStatusCode());
    }

    public function testDiscoveryNon2xx(): void
    {
        $error = $this->assertThrowsExactly(
            NotFoundException::class,
            fn (): string => $this->stubbed($this->stub(404, '{}', 200, '{}'))->getAuthToken()
        );
        $this->assertSame(404, $error->getStatusCode());
        $this->assertInstanceOf(ZitadelException::class, $error);

        $this->assertThrowsExactly(
            InternalServerErrorException::class,
            fn (): string => $this->stubbed($this->stub(500, '{}', 200, '{}'))->getAuthToken()
        );
    }

    public function testDiscoveryMalformed(): void
    {
        foreach (['not json', '[]', '{"issuer":"x"}'] as $body) {
            $error = $this->assertThrowsExactly(
                SerializationException::class,
                fn (): string => $this->stubbed($this->stub(200, $body, 200, '{}'))->getAuthToken()
            );
            $this->assertInstanceOf(ZitadelException::class, $error);
        }
    }

    public function testTokenEndpointRejects(): void
    {
        $error = $this->assertThrowsExactly(
            OAuth2ServerException::class,
            fn (): string => $this->tokenStubbed(401, '{"error":"invalid_client","error_description":"bad"}')->getAuthToken()
        );
        $this->assertSame(401, $error->statusCode);
        $this->assertSame('invalid_client', $error->errorCode);
        $this->assertSame('bad', $error->description);
        $this->assertInstanceOf(ZitadelException::class, $error);

        $raw = $this->assertThrowsExactly(
            OAuth2ServerException::class,
            fn (): string => $this->tokenStubbed(503, 'down')->getAuthToken()
        );
        $this->assertSame(503, $raw->statusCode);
        $this->assertSame('down', $raw->rawBody);
    }

    public function testTokenEndpointUnusable(): void
    {
        foreach (['{"token_type":"Bearer"}', 'not json', '{"access_token":""}'] as $body) {
            $error = $this->assertThrowsExactly(
                OAuth2TokenException::class,
                fn (): string => $this->tokenStubbed(200, $body)->getAuthToken()
            );
            $this->assertInstanceOf(ZitadelException::class, $error);
        }
    }

    public function testRejectsBadScopes(): void
    {
        $builder = ClientCredentialsAuthenticator::builder(self::HOST, 'client-1', 'client-secret');

        foreach ([[], ['open id'], ['']] as $scopes) {
            $this->assertThrowsExactly(InvalidArgumentException::class, fn (): \Zitadel\Client\Auth\ClientCredentialsAuthenticatorBuilder => $builder->scopes(...$scopes));
        }
    }

    public function testJoinsScopes(): void
    {
        /** @var ArrayObject<int, string> $bodies */
        $bodies = new ArrayObject();
        $authenticator = ClientCredentialsAuthenticator::builder(self::HOST, 'client-1', 'client-secret')
            ->scopes('openid', 'profile', 'openid')
            ->build();
        $authenticator->setApiClient($this->stub(200, self::DISCOVERY, 200, '{"access_token":"t"}', $bodies));

        $authenticator->getAuthToken();

        $this->assertStringContainsString('&scope=openid+profile&', (string) $bodies[0]);
    }

    /**
     * Asserts the callable throws exactly the given class (not a subclass) and returns the exception.
     *
     * @template T of \Throwable
     * @param class-string<T> $class
     * @return T
     */
    private function assertThrowsExactly(string $class, callable $action): \Throwable
    {
        try {
            $action();
        } catch (\Throwable $error) {
            $this->assertSame($class, $error::class);
            $this->assertInstanceOf($class, $error);
            return $error;
        }
        $this->fail("Expected $class to be thrown");
    }

    /**
     * An ApiClient that answers discovery and token requests with canned
     * responses and records each token request body in $bodies.
     *
     * @param ArrayObject<int, string>|null $bodies
     */
    private function stub(
        int $discoveryStatus,
        string $discovery,
        int $tokenStatus,
        string $token,
        ?ArrayObject $bodies = null,
    ): ApiClient {
        return new readonly class (
            new ApiHttpResponse($discoveryStatus, $discovery, []),
            new ApiHttpResponse($tokenStatus, $token, []),
            $bodies ?? new ArrayObject(),
        ) implements ApiClient {
            /**
             * @param ArrayObject<int, string> $bodies
             */
            public function __construct(
                private ApiHttpResponse $discovery,
                private ApiHttpResponse $token,
                private ArrayObject $bodies,
            ) {
            }

            #[\Override]
            public function sendRequest(
                string $method,
                string $url,
                array $headers,
                mixed $body,
                bool $noRedirect = false,
            ): ApiHttpResponse {
                if (str_ends_with($url, '/.well-known/openid-configuration')) {
                    return $this->discovery;
                }
                $this->bodies->append(is_string($body) ? $body : '');
                return $this->token;
            }

            #[\Override]
            public function close(): void
            {
            }
        };
    }

    private function stubbed(ApiClient $apiClient, string $host = self::HOST): ClientCredentialsAuthenticator
    {
        $authenticator = ClientCredentialsAuthenticator::builder($host, 'client-1', 'client-secret')->build();
        $authenticator->setApiClient($apiClient);
        return $authenticator;
    }

    private function tokenStubbed(int $status, string $body): ClientCredentialsAuthenticator
    {
        return $this->stubbed($this->stub(200, self::DISCOVERY, $status, $body));
    }
}
