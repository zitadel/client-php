<?php

declare(strict_types=1);

namespace Zitadel\Client;

use Exception;
use Zitadel\Client\Api\ActionServiceApi;
use Zitadel\Client\Api\ApplicationServiceApi;
use Zitadel\Client\Api\AuthorizationServiceApi;
use Zitadel\Client\Api\BetaActionServiceApi;
use Zitadel\Client\Api\BetaAppServiceApi;
use Zitadel\Client\Api\BetaAuthorizationServiceApi;
use Zitadel\Client\Api\BetaFeatureServiceApi;
use Zitadel\Client\Api\BetaInstanceServiceApi;
use Zitadel\Client\Api\BetaInternalPermissionServiceApi;
use Zitadel\Client\Api\BetaOIDCServiceApi;
use Zitadel\Client\Api\BetaOrganizationServiceApi;
use Zitadel\Client\Api\BetaProjectServiceApi;
use Zitadel\Client\Api\BetaSessionServiceApi;
use Zitadel\Client\Api\BetaSettingsServiceApi;
use Zitadel\Client\Api\BetaTelemetryServiceApi;
use Zitadel\Client\Api\BetaUserServiceApi;
use Zitadel\Client\Api\BetaWebKeyServiceApi;
use Zitadel\Client\Api\FeatureServiceApi;
use Zitadel\Client\Api\IdentityProviderServiceApi;
use Zitadel\Client\Api\InstanceServiceApi;
use Zitadel\Client\Api\InternalPermissionServiceApi;
use Zitadel\Client\Api\OIDCServiceApi;
use Zitadel\Client\Api\OrganizationServiceApi;
use Zitadel\Client\Api\ProjectServiceApi;
use Zitadel\Client\Api\SAMLServiceApi;
use Zitadel\Client\Api\SessionServiceApi;
use Zitadel\Client\Api\SettingsServiceApi;
use Zitadel\Client\Api\UserServiceApi;
use Zitadel\Client\Api\WebKeyServiceApi;
use Zitadel\Client\Auth\Authenticator;
use Zitadel\Client\Auth\ClientCredentialsAuthenticator;
use Zitadel\Client\Auth\HttpAwareAuthenticator;
use Zitadel\Client\Auth\PersonalAccessAuthenticator;
use Zitadel\Client\Auth\WebTokenAuthenticator;

class Zitadel
{
    public BetaProjectServiceApi $betaProjects;
    public BetaAppServiceApi $betaApps;
    public BetaOIDCServiceApi $betaOidc;
    public BetaUserServiceApi $betaUsers;
    public BetaOrganizationServiceApi $betaOrganizations;
    public BetaSettingsServiceApi $betaSettings;
    public BetaInternalPermissionServiceApi $betaPermissions;
    public BetaAuthorizationServiceApi $betaAuthorizations;
    public BetaSessionServiceApi $betaSessions;
    public BetaInstanceServiceApi $betaInstance;
    public BetaTelemetryServiceApi $betaTelemetry;
    public BetaFeatureServiceApi $betaFeatures;
    public BetaWebKeyServiceApi $betaWebkeys;
    public BetaActionServiceApi $betaActions;
    public ActionServiceApi $actions;
    public ApplicationServiceApi $applications;
    public AuthorizationServiceApi $authorizations;
    public FeatureServiceApi $features;
    public IdentityProviderServiceApi $idps;
    public InstanceServiceApi $instances;
    public InternalPermissionServiceApi $internalPermissions;
    public OIDCServiceApi $oidc;
    public OrganizationServiceApi $organizations;
    public ProjectServiceApi $projects;
    public SAMLServiceApi $saml;
    public SessionServiceApi $sessions;
    public SettingsServiceApi $settings;
    public UserServiceApi $users;
    public WebKeyServiceApi $webkeys;

    /**
     * @param Authenticator $authenticator The authenticator providing credentials for API calls.
     * @param callable|null $mutateConfig Optional callback to mutate the configuration.
     * @param TransportOptions|null $transportOptions Optional transport options for TLS, proxy, and headers.
     */
    public function __construct(
        Authenticator $authenticator,
        ?callable $mutateConfig = null,
        ?TransportOptions $transportOptions = null,
    ) {
        $resolved = $transportOptions ?? TransportOptions::defaults();
        $apiClient = new DefaultApiClient($resolved);

        if ($authenticator instanceof HttpAwareAuthenticator) {
            $authenticator->setApiClient($apiClient);
        }

        $config = Configuration::builder()
            ->baseUrl($authenticator->getHost())
            ->build();

        if ($mutateConfig !== null) {
            $mutateConfig($config);
        }

        $this->betaProjects = new BetaProjectServiceApi($apiClient, $config, $authenticator);
        $this->betaApps = new BetaAppServiceApi($apiClient, $config, $authenticator);
        $this->betaOidc = new BetaOIDCServiceApi($apiClient, $config, $authenticator);
        $this->betaUsers = new BetaUserServiceApi($apiClient, $config, $authenticator);
        $this->betaOrganizations = new BetaOrganizationServiceApi($apiClient, $config, $authenticator);
        $this->betaSettings = new BetaSettingsServiceApi($apiClient, $config, $authenticator);
        $this->betaPermissions = new BetaInternalPermissionServiceApi($apiClient, $config, $authenticator);
        $this->betaAuthorizations = new BetaAuthorizationServiceApi($apiClient, $config, $authenticator);
        $this->betaSessions = new BetaSessionServiceApi($apiClient, $config, $authenticator);
        $this->betaInstance = new BetaInstanceServiceApi($apiClient, $config, $authenticator);
        $this->betaTelemetry = new BetaTelemetryServiceApi($apiClient, $config, $authenticator);
        $this->betaFeatures = new BetaFeatureServiceApi($apiClient, $config, $authenticator);
        $this->betaWebkeys = new BetaWebKeyServiceApi($apiClient, $config, $authenticator);
        $this->betaActions = new BetaActionServiceApi($apiClient, $config, $authenticator);
        $this->actions = new ActionServiceApi($apiClient, $config, $authenticator);
        $this->applications = new ApplicationServiceApi($apiClient, $config, $authenticator);
        $this->authorizations = new AuthorizationServiceApi($apiClient, $config, $authenticator);
        $this->features = new FeatureServiceApi($apiClient, $config, $authenticator);
        $this->idps = new IdentityProviderServiceApi($apiClient, $config, $authenticator);
        $this->instances = new InstanceServiceApi($apiClient, $config, $authenticator);
        $this->internalPermissions = new InternalPermissionServiceApi($apiClient, $config, $authenticator);
        $this->oidc = new OIDCServiceApi($apiClient, $config, $authenticator);
        $this->organizations = new OrganizationServiceApi($apiClient, $config, $authenticator);
        $this->projects = new ProjectServiceApi($apiClient, $config, $authenticator);
        $this->saml = new SAMLServiceApi($apiClient, $config, $authenticator);
        $this->sessions = new SessionServiceApi($apiClient, $config, $authenticator);
        $this->settings = new SettingsServiceApi($apiClient, $config, $authenticator);
        $this->users = new UserServiceApi($apiClient, $config, $authenticator);
        $this->webkeys = new WebKeyServiceApi($apiClient, $config, $authenticator);
    }

    /**
     * Initialize the SDK with a Personal Access Token (PAT).
     *
     * @param string $host API URL (e.g. "https://api.zitadel.example.com").
     * @param string $accessToken Personal Access Token for Bearer authentication.
     * @param TransportOptions|null $transportOptions Optional transport options for TLS, proxy, and headers.
     * @return self Configured Zitadel client instance.
     * @see https://zitadel.com/docs/guides/integrate/service-users/personal-access-token
     */
    public static function withAccessToken(
        string $host,
        string $accessToken,
        ?TransportOptions $transportOptions = null,
    ): self {
        $resolved = $transportOptions ?? TransportOptions::defaults();
        return new self(
            new PersonalAccessAuthenticator($host, $accessToken),
            null,
            $resolved,
        );
    }

    /**
     * Initialize the SDK using OAuth2 Client Credentials flow.
     *
     * @param string $host API URL.
     * @param string $clientId OAuth2 client identifier.
     * @param string $clientSecret OAuth2 client secret.
     * @param TransportOptions|null $transportOptions Optional transport options for TLS, proxy, and headers.
     * @return self Configured Zitadel client instance with token auto-refresh.
     * @throws Exception If token retrieval fails.
     * @see https://zitadel.com/docs/guides/integrate/service-users/client-credentials
     */
    public static function withClientCredentials(
        string $host,
        string $clientId,
        string $clientSecret,
        ?TransportOptions $transportOptions = null,
    ): self {
        $resolved = $transportOptions ?? TransportOptions::defaults();
        return new self(
            ClientCredentialsAuthenticator::builder($host, $clientId, $clientSecret, $resolved)
                ->build(),
            null,
            $resolved,
        );
    }

    /**
     * Initialize the SDK via Private Key JWT assertion.
     *
     * @param string $host API URL.
     * @param string $keyFile Path to service account JSON or PEM key file.
     * @param TransportOptions|null $transportOptions Optional transport options for TLS, proxy, and headers.
     * @return self Configured Zitadel client instance using JWT assertion.
     * @throws Exception If key parsing or token exchange fails.
     * @see https://zitadel.com/docs/guides/integrate/service-users/private-key-jwt
     */
    public static function withPrivateKey(
        string $host,
        string $keyFile,
        ?TransportOptions $transportOptions = null,
    ): self {
        $resolved = $transportOptions ?? TransportOptions::defaults();
        return new self(
            WebTokenAuthenticator::fromJson($host, $keyFile, $resolved),
            null,
            $resolved,
        );
    }
}
