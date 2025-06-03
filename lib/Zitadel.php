<?php

namespace Zitadel\Client;

use Exception;
use Zitadel\Client\Api\BetaActionServiceApi;
use Zitadel\Client\Api\BetaWebKeyServiceApi;
use Zitadel\Client\Api\FeatureServiceApi;
use Zitadel\Client\Api\IdentityProviderServiceApi;
use Zitadel\Client\Api\OIDCServiceApi;
use Zitadel\Client\Api\OrganizationServiceApi;
use Zitadel\Client\Api\SAMLServiceApi;
use Zitadel\Client\Api\SessionServiceApi;
use Zitadel\Client\Api\SettingsServiceApi;
use Zitadel\Client\Api\UserServiceApi;
use Zitadel\Client\Auth\Authenticator;
use Zitadel\Client\Auth\ClientCredentialsAuthenticator;
use Zitadel\Client\Auth\PersonalAccessAuthenticator;
use Zitadel\Client\Auth\WebTokenAuthenticator;

class Zitadel
{
    public BetaActionServiceApi $actions;
    public FeatureServiceApi $features;
    public IdentityProviderServiceApi $idps;
    public OidcServiceApi $oidc;
    public OrganizationServiceApi $organizations;
    public SAMLServiceApi $saml;
    public SessionServiceApi $sessions;
    public SettingsServiceApi $settings;
    public UserServiceApi $users;
    public BetaWebKeyServiceApi $webkeys;

    public function __construct(Authenticator $authenticator, ?callable $mutateConfig = null)
    {
        $config = new Configuration($authenticator);
        $mutateConfig ??= static function (Configuration $config): void {
            // No mutation by default.
        };
        $mutateConfig($config);

        $this->actions = new BetaActionServiceApi(null, $config);
        $this->features = new FeatureServiceApi(null, $config);
        $this->idps = new IdentityProviderServiceApi(null, $config);
        $this->oidc = new OidcServiceApi(null, $config);
        $this->organizations = new OrganizationServiceApi(null, $config);
        $this->saml = new SAMLServiceApi(null, $config);
        $this->sessions = new SessionServiceApi(null, $config);
        $this->settings = new SettingsServiceApi(null, $config);
        $this->users = new UserServiceApi(null, $config);
        $this->webkeys = new BetaWebKeyServiceApi(null, $config);
    }

    /**
     * Initialize the SDK with a Personal Access Token (PAT).
     *
     * @param string $host API URL (e.g. "https://api.zitadel.example.com").
     * @param string $accessToken Personal Access Token for Bearer authentication.
     * @return self Configured Zitadel client instance.
     * @see https://zitadel.com/docs/guides/integrate/service-users/personal-access-token
     */
    public static function withAccessToken(string $host, string $accessToken): self
    {
        return new self(new PersonalAccessAuthenticator($host, $accessToken));
    }

    /**
     * Initialize the SDK using OAuth2 Client Credentials flow.
     *
     * @param string $host API URL.
     * @param string $clientId OAuth2 client identifier.
     * @param string $clientSecret OAuth2 client secret.
     * @return self Configured Zitadel client instance with token auto-refresh.
     * @throws Exception If token retrieval fails.
     * @see https://zitadel.com/docs/guides/integrate/service-users/client-credentials
     */
    public static function withClientCredentials(string $host, string $clientId, string $clientSecret): self
    {
        return new self(
            ClientCredentialsAuthenticator::builder($host, $clientId, $clientSecret)
                ->build()
        );
    }

    /**
     * Initialize the SDK via Private Key JWT assertion.
     *
     * @param string $host API URL.
     * @param string $keyFile Path to service account JSON or PEM key file.
     * @return self Configured Zitadel client instance using JWT assertion.
     * @throws Exception If key parsing or token exchange fails.
     * @see https://zitadel.com/docs/guides/integrate/service-users/private-key-jwt
     */
    public static function withPrivateKey(string $host, string $keyFile): self
    {
        return new self(WebTokenAuthenticator::fromJson($host, $keyFile));
    }
}
