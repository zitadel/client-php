<?php

namespace Zitadel\Client;

use Zitadel\Client\Api\FeatureServiceApi;
use Zitadel\Client\Api\IdentityProviderServiceApi;
use Zitadel\Client\Api\OIDCServiceApi;
use Zitadel\Client\Api\OrganizationServiceApi;
use Zitadel\Client\Api\SessionServiceApi;
use Zitadel\Client\Api\SettingsServiceApi;
use Zitadel\Client\Api\UserServiceApi;
use Zitadel\Client\Auth\Authenticator;
use Zitadel\Client\Auth\ClientCredentialsAuthenticator;
use Zitadel\Client\Auth\PersonalAccessAuthenticator;
use Zitadel\Client\Auth\WebTokenAuthenticator;

class Zitadel
{
    public FeatureServiceApi $features;
    public IdentityProviderServiceApi $idps;
    public OidcServiceApi $oidc;
    public OrganizationServiceApi $organizations;
    public SessionServiceApi $sessions;
    public SettingsServiceApi $settings;
    public UserServiceApi $users;

    public function __construct(Authenticator $authenticator, ?callable $mutateConfig = null)
    {
        $config = new Configuration($authenticator);
        $mutateConfig ??= static function (Configuration $config): void {
            // No mutation by default.
        };
        $mutateConfig($config);

        $this->features = new FeatureServiceApi(null, $config);
        $this->idps = new IdentityProviderServiceApi(null, $config);
        $this->oidc = new OidcServiceApi(null, $config);
        $this->organizations = new OrganizationServiceApi(null, $config);
        $this->sessions = new SessionServiceApi(null, $config);
        $this->settings = new SettingsServiceApi(null, $config);
        $this->users = new UserServiceApi(null, $config);
    }

    /**
     * Initialize Zitadel with a Personal Access Token.
     */
    public static function withAccessToken(string $host, string $accessToken): self
    {
        return new self(new PersonalAccessAuthenticator($host, $accessToken));
    }

    /**
     * Initialize Zitadel with Client Credentials.
     * @throws \Exception
     */
    public static function withClientCredentials(string $host, string $clientId, string $clientSecret): self
    {
        return new self(ClientCredentialsAuthenticator::builder($host, $clientId, $clientSecret)->build());
    }

    /**
     * Initialize Zitadel with a Private Key.
     * @throws \Exception
     */
    public static function withPrivateKey(string $host, string $keyFile): self
    {
        return new self(WebTokenAuthenticator::fromJson($host, $keyFile));
    }
}
