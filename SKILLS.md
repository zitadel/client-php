# Zitadel SDK - AI Agent Reference

## Installation

Add the SDK to your project via Composer:

```bash
composer require zitadel/client
```

## Quick Start

```php
use Zitadel\Client\Zitadel;

$client = Zitadel::withToken('https://api.example.com', 'your-token');
```

## Authentication

All authentication is handled via `Authenticator` implementations passed to the client constructor.

### Bearer Token

```php
use Zitadel\Client\Auth\BearerAuthenticator;

$authenticator = new BearerAuthenticator('https://api.example.com', 'your-token');
$client = new Zitadel($authenticator);
```

## Servers

If the OpenAPI spec defines multiple servers, the generated `Zitadel\Client\Servers` class exposes each as a `ServerConfiguration` static method (e.g., `Servers::server0()`, `Servers::server1()`, ...) plus a `Servers::all()` array. Pass the desired server's URL to the client:

```php
use Zitadel\Client\Servers;

$client = Zitadel::withToken(Servers::server0()->getUrl(), 'your-token');
```

## Testing

The `Authenticator` interface is the seam for tests: substitute a fake authenticator that returns a known header map, and assert your code calls the API the way you expect.

```php
use Zitadel\Client\Auth\Authenticator;

$fake = new class implements Authenticator {
    public function getHost(): string { return 'https://api.example.com'; }
    public function getAuthHeaders(): array {
        return ['Authorization' => 'Bearer test-token'];
    }
    public function getQueryParams(): array { return []; }
    public function getCookieParams(): array { return []; }
};

$client = new Zitadel($fake);
```

## Error Handling

All API errors derive from `ApiException`. The error hierarchy is:

- `ApiException` (base)
  - `ClientException` (4xx)
    - `BadRequestException` (400)
    - `UnauthorizedException` (401)
    - `ForbiddenException` (403)
    - `NotFoundException` (404)
    - `ConflictException` (409)
    - `UnprocessableEntityException` (422)
  - `ServerException` (5xx)
    - `InternalServerErrorException` (500)
  - `NetworkException` (no HTTP response, status 0)
    - `NetworkTimeoutException` (the request timed out, status 0)

```php
use Zitadel\Client\Zitadel;
use Zitadel\Client\Errors\ApiException;
use Zitadel\Client\Errors\NotFoundException;
use Zitadel\Client\Errors\ClientException;
use Zitadel\Client\Errors\ServerException;

function activatePublicKeyOrReport(Zitadel $client, \Zitadel\Client\Models\ActionServiceActivatePublicKeyRequest $actionServiceActivatePublicKeyRequest): void
{
    try {
        $client->actionService->activatePublicKey($actionServiceActivatePublicKeyRequest);
    } catch (NotFoundException $e) {
        echo "Not found: " . $e->getMessage();
    } catch (ClientException $e) {
        echo "Client error " . $e->getStatusCode() . ": " . $e->getMessage();
    } catch (ServerException $e) {
        echo "Server error: " . $e->getMessage();
    } catch (ApiException $e) {
        echo "API error: " . $e->getMessage();
    }
}
```

## Configuration

### Custom Transport Options

```php
use Zitadel\Client\TransportOptions;

$transport = TransportOptions::builder()
    ->proxy('http://proxy:3128')
    ->timeout(5000)
    ->build();

$client = new Zitadel($authenticator, $transport);
```

## API Methods

Each API group is exposed as a typed property on the client (e.g., `$client->actionService`). API classes have methods that correspond to OpenAPI operations, accepting typed request parameters and returning typed response models.

## Models

Models are generated as PHP classes under the `Zitadel\Client\Models` namespace.

```php
use Zitadel\Client\Models\ActionServiceActivatePublicKeyRequest;

$model = new ActionServiceActivatePublicKeyRequest();
```

## Binary / File Uploads

File upload parameters are typed as `SplFileInfo`. Binary response bodies are returned as `string`.

## Comment Style

Never place a comment on the same line as code. Use block comments (`/* ... */`); PHPDoc (`/** ... */`) is fine.

```good
/* This explains the logic */
$x = 1;
```

```bad
// This explains the logic
$x = 1;
```
