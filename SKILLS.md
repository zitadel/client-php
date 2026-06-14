# Zitadel SDK SDK - AI Agent Reference

## Installation

Add the SDK to your project via Composer:

```bash
composer require <vendor>/<package-name>
```

## Quick Start

```php
use Zitadel\Client\Client;

$client = Client::withToken('https://api.example.com', 'your-token');
```

## Authentication

All authentication is handled via `Authenticator` implementations passed to the client constructor.

### Bearer Token

```php
use Zitadel\Client\Auth\BearerAuthenticator;

$authenticator = new BearerAuthenticator('https://api.example.com', 'your-token');
$client = new Client($authenticator);
```

## Servers

If the OpenAPI spec defines multiple servers, the generated `Zitadel\Client\Servers` class exposes each as a `ServerConfiguration` constant (e.g., `Servers::SERVER_0`, `Servers::SERVER_1`, ...) plus an `Servers::ALL` array. Pass the desired server's URL to the client:

```php
use Zitadel\Client\Servers;

$client = Client::withToken(Servers::SERVER_0->url(), 'your-token');
```

## Testing

The `Authenticator` interface is the seam for tests: substitute a fake authenticator that returns a known header map, and assert your code calls the API the way you expect.

```php
$fake = new class implements Zitadel\Client\Auth\Authenticator {
    public function getAuthHeaders(RequestContext $request): array {
        return ['Authorization' => 'Bearer test-token'];
    }
    public function getHost(): string { return 'https://api.example.com'; }
};

$client = new Client($fake);
```

## Error Handling

All API errors extend `ApiException`. The exception hierarchy is:

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

```php
use Zitadel\Client\ApiException;
use Zitadel\Client\Errors\NotFoundException;
use Zitadel\Client\Errors\ClientException;
use Zitadel\Client\Errors\ServerException;

try {
    $result = $client->petApi->getPetById($petId);
} catch (NotFoundException $e) {
    echo "Not found: " . $e->getMessage();
} catch (ClientException $e) {
    echo "Client error " . $e->getStatusCode() . ": " . $e->getMessage();
} catch (ServerException $e) {
    echo "Server error: " . $e->getMessage();
} catch (ApiException $e) {
    echo "API error: " . $e->getMessage();
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

$client = new Client($authenticator, $transport);
```

## API Methods

Each API group is exposed as a typed property on the client (e.g., `$client->petApi`). API classes have methods that correspond to OpenAPI operations, accepting typed request parameters and returning typed response models.

## Models

Models are generated as PHP classes under the `Zitadel\Client\Models` namespace.

```php
use Zitadel\Client\Models\Pet;

$pet = new Pet(name: 'Fido', status: 'available');
```

## Binary / File Uploads

File upload parameters accept `SplFileInfo` or file path strings. Binary response bodies are returned as `string`.

## Comment Style

Never use inline comments (`//`). Always use block comments (`/* ... */`). PHPDoc `/** ... */` is fine.

```good
/* This explains the logic */
$x = 1;
```

```bad
// This explains the logic
$x = 1;
```
