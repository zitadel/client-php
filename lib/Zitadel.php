<?php

namespace Zitadel\Client;

use Exception;
use GuzzleHttp\Client;
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
use Zitadel\Client\Auth\PersonalAccessAuthenticator;
use Zitadel\Client\Auth\WebTokenAuthenticator;

class Zitadel
{
    /** @var BetaProjectServiceApi */
    public BetaProjectServiceApi $betaProjects;
    /** @var BetaAppServiceApi */
    public BetaAppServiceApi $betaApps;
    /** @var BetaOIDCServiceApi */
    public BetaOIDCServiceApi $betaOidc;
    /** @var BetaUserServiceApi */
    public BetaUserServiceApi $betaUsers;
    /** @var BetaOrganizationServiceApi */
    public BetaOrganizationServiceApi $betaOrganizations;
    /** @var BetaSettingsServiceApi */
    public BetaSettingsServiceApi $betaSettings;
    /** @var BetaInternalPermissionServiceApi */
    public BetaInternalPermissionServiceApi $betaPermissions;
    /** @var BetaAuthorizationServiceApi */
    public BetaAuthorizationServiceApi $betaAuthorizations;
    /** @var BetaSessionServiceApi */
    public BetaSessionServiceApi $betaSessions;
    /** @var BetaInstanceServiceApi */
    public BetaInstanceServiceApi $betaInstance;
    /** @var BetaTelemetryServiceApi */
    public BetaTelemetryServiceApi $betaTelemetry;
    /** @var BetaFeatureServiceApi */
    public BetaFeatureServiceApi $betaFeatures;
    /** @var BetaWebKeyServiceApi */
    public BetaWebKeyServiceApi $betaWebkeys;
    /** @var BetaActionServiceApi */
    public BetaActionServiceApi $betaActions;
    /** @var ActionServiceApi */
    public ActionServiceApi $actions;
    /** @var ApplicationServiceApi */
    public ApplicationServiceApi $applications;
    /** @var AuthorizationServiceApi */
    public AuthorizationServiceApi $authorizations;
    /** @var FeatureServiceApi */
    public FeatureServiceApi $features;
    /** @var IdentityProviderServiceApi */
    public IdentityProviderServiceApi $idps;
    /** @var InstanceServiceApi */
    public InstanceServiceApi $instances;
    /** @var InternalPermissionServiceApi */
    public InternalPermissionServiceApi $internalPermissions;
    /** @var OIDCServiceApi */
    public OIDCServiceApi $oidc;
    /** @var OrganizationServiceApi */
    public OrganizationServiceApi $organizations;
    /** @var ProjectServiceApi */
    public ProjectServiceApi $projects;
    /** @var SAMLServiceApi */
    public SAMLServiceApi $saml;
    /** @var SessionServiceApi */
    public SessionServiceApi $sessions;
    /** @var SettingsServiceApi */
    public SettingsServiceApi $settings;
    /** @var UserServiceApi */
    public UserServiceApi $users;
    /** @var WebKeyServiceApi */
    public WebKeyServiceApi $webkeys;

    /**
     * @param Authenticator $authenticator The authenticator providing credentials for API calls.
     * @param TransportOptions|null $transportOptions Optional transport options for TLS, proxy, and headers.
     */
    public function __construct(Authenticator $authenticator, ?TransportOptions $transportOptions = null)
    {
        $resolved = $transportOptions ?? TransportOptions::defaults();
        $config = new Configuration($authenticator);

        $guzzleOpts = array_merge(['http_errors' => false], $resolved->toGuzzleOptions());
        $client = new Client($guzzleOpts);

        $this->betaProjects = new BetaProjectServiceApi($client, $config);
        $this->betaApps = new BetaAppServiceApi($client, $config);
        $this->betaOidc = new BetaOIDCServiceApi($client, $config);
        $this->betaUsers = new BetaUserServiceApi($client, $config);
        $this->betaOrganizations = new BetaOrganizationServiceApi($client, $config);
        $this->betaSettings = new BetaSettingsServiceApi($client, $config);
        $this->betaPermissions = new BetaInternalPermissionServiceApi($client, $config);
        $this->betaAuthorizations = new BetaAuthorizationServiceApi($client, $config);
        $this->betaSessions = new BetaSessionServiceApi($client, $config);
        $this->betaInstance = new BetaInstanceServiceApi($client, $config);
        $this->betaTelemetry = new BetaTelemetryServiceApi($client, $config);
        $this->betaFeatures = new BetaFeatureServiceApi($client, $config);
        $this->betaWebkeys = new BetaWebKeyServiceApi($client, $config);
        $this->betaActions = new BetaActionServiceApi($client, $config);
        $this->actions = new ActionServiceApi($client, $config);
        $this->applications = new ApplicationServiceApi($client, $config);
        $this->authorizations = new AuthorizationServiceApi($client, $config);
        $this->features = new FeatureServiceApi($client, $config);
        $this->idps = new IdentityProviderServiceApi($client, $config);
        $this->instances = new InstanceServiceApi($client, $config);
        $this->internalPermissions = new InternalPermissionServiceApi($client, $config);
        $this->oidc = new OIDCServiceApi($client, $config);
        $this->organizations = new OrganizationServiceApi($client, $config);
        $this->projects = new ProjectServiceApi($client, $config);
        $this->saml = new SAMLServiceApi($client, $config);
        $this->sessions = new SessionServiceApi($client, $config);
        $this->settings = new SettingsServiceApi($client, $config);
        $this->users = new UserServiceApi($client, $config);
        $this->webkeys = new WebKeyServiceApi($client, $config);
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
            $resolved,
        );
    }

}
