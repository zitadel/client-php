<?php

namespace Zitadel\Client\Auth;

use Exception;
use GuzzleHttp\Client;
use Throwable;

/**
 * OAuth2 Client Credentials Authenticator.
 *
 * Implements the OAuth2 client credentials grant to obtain an access token.
 */
class ClientCredentialsAuthenticator extends OAuthAuthenticator
{
  /**
   * The OAuth2 client secret.
   *
   * @var string
   */
  private string $clientSecret;

  /**
   * Guzzle HTTP client.
   *
   * @var Client
   */
  private Client $httpClient;

  /**
   * ClientCredentialsAuthenticator constructor.
   *
   * @param string $host The base URL for the API endpoints.
   * @param string $clientId The OAuth2 client identifier.
   * @param string $clientSecret The OAuth2 client secret.
   * @param string|null $tokenUrl The URL of the OAuth2 token endpoint. Defaults to "/oauth/v2/token".
   * @param string $scope The scope for the token request. Defaults to a predefined scope string.
   */
  public function __construct(
    string  $host,
    string  $clientId,
    string  $clientSecret,
    ?string $tokenUrl,
    string  $scope = 'openid urn:zitadel:iam:org:project:id:myprojectid:aud additional_scope'
  )
  {
    // If tokenUrl is relative, prepend the host.
    $fullTokenUrl = strpos($tokenUrl, '/') === 0 ? $host . $tokenUrl : $tokenUrl;
    parent::__construct($host, $clientId, $fullTokenUrl, $scope);
    $this->clientSecret = $clientSecret;
    $this->httpClient = new Client();
  }

  /**
   * Refresh the access token using the client credentials grant.
   *
   * This method sends a POST request to the token endpoint with the client credentials.
   *
   * @return void
   * @throws Exception if the HTTP request fails or the response is invalid.
   */
  public function refreshToken(): void
  {
    $postData = http_build_query([
      'grant_type' => 'client_credentials',
      'client_id' => $this->clientId,
      'client_secret' => $this->clientSecret,
      'scope' => $this->scope
    ]);

    $this->token = $this->sendPostRequest($this->tokenUrl, $postData);
  }

  /**
   * Sends a POST request to the given URL with the provided data using Guzzle.
   *
   * @param string $url The endpoint URL.
   * @param string $postData The URL-encoded POST data.
   * @return array The JSON-decoded response as an associative array.
   * @throws Exception if the request fails or returns invalid JSON.
   */
  private function sendPostRequest(string $url, string $postData): array
  {
    try {
      $response = $this->httpClient->post($url, [
        'headers' => [
          'Content-Type' => 'application/x-www-form-urlencoded'
        ],
        'body' => $postData
      ]);
    } catch (Throwable $e) {
      throw new Exception('HTTP request failed: ' . $e->getMessage());
    }

    $body = (string)$response->getBody();
    $decoded = json_decode($body, true);
    if ($decoded === null) {
      throw new Exception('Invalid JSON response.');
    }

    return $decoded;
  }
}
