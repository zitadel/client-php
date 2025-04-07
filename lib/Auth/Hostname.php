<?php

namespace Zitadel\Client\Auth;

use League\Uri\Uri;

class Hostname
{
  private Uri $baseUri;

  public function __construct(string $input)
  {
    $normalized = preg_match('/^[a-z][a-z0-9+.-]*:\/\//i', $input)
      ? $input
      : 'https://' . $input;

    $uri = Uri::new($normalized);
    $this->baseUri = $uri->withScheme(getenv('APP_ENV') === 'test' ? 'http': 'https')->withPort($uri->getPort() ?? 443);
  }

  public function getEndpoint(): string
  {
    return $this->baseUri->toString();
  }

  public function getEndpointWithPath(string $path): Uri
  {
    $normalizedPath = '/' . ltrim($path, '/');
    return $this->baseUri->withPath($normalizedPath);
  }
}
