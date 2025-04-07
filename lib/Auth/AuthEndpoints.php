<?php

namespace Zitadel\Client\Auth;

use League\Uri\Uri;

final class AuthEndpoints
{
  public Uri $urlResourceOwnerDetails;
  public Uri $urlAuthorize;
  public Uri $urlAccessToken;

  function __construct(
    Uri $urlAccessToken,
    Uri $urlAuthorize,
    Uri $urlResourceOwnerDetails
  )
  {
    $this->urlAccessToken = $urlAccessToken;
    $this->urlAuthorize = $urlAuthorize;
    $this->urlResourceOwnerDetails = $urlResourceOwnerDetails;
    //
  }

  static function getInstance(Hostname $hostName): self
  {
    return new self(
      $hostName->getEndpointWithPath('/oauth/v2/token'),
      $hostName->getEndpointWithPath('/oauth/v2/authorize'),
      $hostName->getEndpointWithPath('/oauth/v2/userinfo')
    );
  }
}
