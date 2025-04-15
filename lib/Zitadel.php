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

class Zitadel
{
  public FeatureServiceApi $features;
  public IdentityProviderServiceApi $idps;
  public OidcServiceApi $oidc;
  public OrganizationServiceApi $organizations;
  public SessionServiceApi $sessions;
  public SettingsServiceApi $settings;
  public UserServiceApi $users;

  public function __construct(Authenticator $authenticator)
  {
    $config = new Configuration($authenticator);

    $mutateConfig ??= function ($config) {
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
}
