<?php

declare(strict_types=1);

// phpcs:ignoreFile

namespace Zitadel\Client\Test;

use Zitadel\Client\ApiException;
use Zitadel\Client\Psr18ApiClient;
use Zitadel\Client\TransportOptions;
use Zitadel\Client\TransportOptionsBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * A stand-in model part for the multipart serialization test below. It is
 * declared here rather than taken from the generated models so the test holds
 * for EVERY spec this SDK is generated from — no spec is guaranteed to contain
 * a model with these properties. It is shaped exactly like a generated model:
 * the PHP property names ARE the wire names, which is what the SDK's
 * ObjectSerializer emits (its normalizer is built without a name converter).
 */
final class MultipartModelPart
{
    #[SerializedName('isPrimary')]
    public ?bool $isPrimary = null;

    #[SerializedName('takenAt')]
    public ?\DateTime $takenAt = null;

    public function __construct(?bool $isPrimary = null, ?\DateTime $takenAt = null)
    {
        $this->isPrimary = $isPrimary;
        $this->takenAt = $takenAt;
    }
}

/**
 * A minimal PSR-18 client that replays a queue of canned responses and records
 * every {@see RequestInterface} it is handed, so tests can assert what the
 * Psr18ApiClient put on the wire. Mirrors the role of the Symfony MockHttpClient
 * in the DefaultApiClient unit tests, exercising the SAME shared orchestration
 * ({@see Zitadel\Client\AbstractApiClient}) through the PSR-18 transport.
 */
final class StubPsr18Client implements ClientInterface
{
    /** @var list<ResponseInterface> */
    private array $responses;

    /** @var list<RequestInterface> */
    public array $requests = [];

    public function __construct(ResponseInterface ...$responses)
    {
        $this->responses = array_values($responses);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        if ($this->responses === []) {
            throw new class ('no stubbed response remaining') extends \RuntimeException implements ClientExceptionInterface {
            };
        }

        return array_shift($this->responses);
    }
}

/**
 * Build a PSR-7 response with the given status, body, and headers.
 *
 * @param array<string, string> $headers
 */
function psr18Response(int $status, string $body = '', array $headers = []): ResponseInterface
{
    $factory = new Psr17Factory();
    $response = $factory->createResponse($status)->withBody($factory->createStream($body));
    foreach ($headers as $name => $value) {
        $response = $response->withHeader($name, $value);
    }

    return $response;
}

function newPsr18Client(StubPsr18Client $stub, ?TransportOptions $transport = null): Psr18ApiClient
{
    $factory = new Psr17Factory();

    return new Psr18ApiClient($stub, $factory, $factory, $transport);
}

test('psr18 sends get request and returns response', function (): void {
    $stub = new StubPsr18Client(psr18Response(200, '{"method":"GET"}'));
    $client = newPsr18Client($stub);

    $response = $client->sendRequest('GET', 'http://example.com/echo', [], null);

    expect($response->statusCode)->toBe(200);
    expect($response->body)->toContain('GET');
    expect($stub->requests[0]->getMethod())->toBe('GET');
});

test('psr18 sends post with json body', function (): void {
    $stub = new StubPsr18Client(psr18Response(200, 'ok'));
    $client = newPsr18Client($stub);

    $response = $client->sendRequest(
        'POST',
        'http://example.com/echo',
        ['Content-Type' => 'application/json'],
        '{"key":"value"}'
    );

    expect($response->statusCode)->toBe(200);
    expect((string) $stub->requests[0]->getBody())->toBe('{"key":"value"}');
    expect($stub->requests[0]->getHeaderLine('Content-Type'))->toBe('application/json');
});

test('psr18 returns response headers lowercased', function (): void {
    $stub = new StubPsr18Client(psr18Response(200, 'ok', ['X-Test-Header' => 'test-value']));
    $client = newPsr18Client($stub);

    $response = $client->sendRequest('GET', 'http://example.com/echo', [], null);

    expect($response->headers)->toHaveKey('x-test-header');
    expect($response->headers['x-test-header'])->toBe('test-value');
});

test('psr18 returns non 2xx status code', function (): void {
    $stub = new StubPsr18Client(psr18Response(404, 'not found'));
    $client = newPsr18Client($stub);

    $response = $client->sendRequest('GET', 'http://example.com/not-found', [], null);

    expect($response->statusCode)->toBe(404);
    expect($response->body)->toBe('not found');
});

test('psr18 joins multi value response headers', function (): void {
    $factory = new Psr17Factory();
    $response = $factory->createResponse(200)
        ->withBody($factory->createStream('ok'))
        ->withHeader('X-Custom-Value', ['val1', 'val2']);
    $stub = new StubPsr18Client($response);
    $client = newPsr18Client($stub);

    $result = $client->sendRequest('GET', 'http://example.com/multi-header', [], null);

    expect($result->headers)->toHaveKey('x-custom-value');
    expect($result->headers['x-custom-value'])->toContain('val1');
    expect($result->headers['x-custom-value'])->toContain('val2');
});

test('psr18 injects custom user agent', function (): void {
    $stub = new StubPsr18Client(psr18Response(200, '{}'));
    $transport = new TransportOptionsBuilder()->userAgent('MyApp/1.0')->build();
    $client = newPsr18Client($stub, $transport);

    $client->sendRequest('GET', 'http://example.com/test', [], null);

    expect($stub->requests[0]->getHeaderLine('User-Agent'))->toBe('MyApp/1.0');
});

test('psr18 injects request id matching uuid v4', function (): void {
    $stub = new StubPsr18Client(psr18Response(200, '{}'));
    $transport = new TransportOptionsBuilder()->injectRequestId(true)->build();
    $client = newPsr18Client($stub, $transport);

    $client->sendRequest('GET', 'http://example.com/test', [], null);

    expect($stub->requests[0]->getHeaderLine('X-Request-ID'))
        ->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

test('psr18 does not inject request id when disabled', function (): void {
    $stub = new StubPsr18Client(psr18Response(200, '{}'));
    $transport = new TransportOptionsBuilder()->injectRequestId(false)->build();
    $client = newPsr18Client($stub, $transport);

    $client->sendRequest('GET', 'http://example.com/test', [], null);

    expect($stub->requests[0]->hasHeader('X-Request-ID'))->toBeFalse();
});

test('psr18 does not override caller request id', function (): void {
    $stub = new StubPsr18Client(psr18Response(200, '{}'));
    $transport = new TransportOptionsBuilder()->injectRequestId(true)->build();
    $client = newPsr18Client($stub, $transport);

    $client->sendRequest('GET', 'http://example.com/test', ['X-Request-ID' => 'caller-id'], null);

    expect($stub->requests[0]->getHeaderLine('X-Request-ID'))->toBe('caller-id');
});

test('psr18 includes transport default headers', function (): void {
    $stub = new StubPsr18Client(psr18Response(200, '{}'));
    $transport = new TransportOptionsBuilder()->defaultHeader('X-Custom', 'custom-value')->build();
    $client = newPsr18Client($stub, $transport);

    $client->sendRequest('GET', 'http://example.com/test', [], null);

    expect($stub->requests[0]->getHeaderLine('X-Custom'))->toBe('custom-value');
});

test('psr18 caller headers override defaults', function (): void {
    $stub = new StubPsr18Client(psr18Response(200, '{}'));
    $transport = new TransportOptionsBuilder()->defaultHeader('Accept', 'text/plain')->build();
    $client = newPsr18Client($stub, $transport);

    $client->sendRequest('GET', 'http://example.com/test', ['Accept' => 'application/json'], null);

    expect($stub->requests[0]->getHeaderLine('Accept'))->toBe('application/json');
});

test('psr18 decodes iso 8859 1 body to utf 8 when charset declared', function (): void {
    $stub = new StubPsr18Client(
        psr18Response(200, "\xE9", ['Content-Type' => 'text/plain; charset=ISO-8859-1'])
    );
    $client = newPsr18Client($stub);

    $response = $client->sendRequest('GET', 'http://example.com/iso', [], null);

    expect($response->body)->toBe("\xC3\xA9"); // UTF-8 'é'
});

test('psr18 cross origin redirect strips authorization and cookie', function (): void {
    $stub = new StubPsr18Client(
        psr18Response(302, '', ['Location' => 'https://other.example.com/final']),
        psr18Response(200, 'ok')
    );
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = newPsr18Client($stub, $transport);

    $client->sendRequest('GET', 'https://api.example.com/start', [
        'Authorization' => 'Bearer secret',
        'Cookie' => 'session=xyz',
        'X-Trace' => 'keep',
    ], null);

    expect($stub->requests[1]->hasHeader('Authorization'))->toBeFalse();
    expect($stub->requests[1]->hasHeader('Cookie'))->toBeFalse();
    expect($stub->requests[1]->hasHeader('X-Trace'))->toBeTrue();
});

test('psr18 no redirect arg surfaces 302 to caller', function (): void {
    $stub = new StubPsr18Client(
        psr18Response(302, '', ['Location' => 'https://attacker.example.com/token'])
    );
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = newPsr18Client($stub, $transport);

    $response = $client->sendRequest('POST', 'https://auth.example.com/token', [], 'grant_type=x', noRedirect: true);

    expect($response->statusCode)->toBe(302);
});

test('psr18 follows redirect when not suppressed', function (): void {
    $stub = new StubPsr18Client(
        psr18Response(302, '', ['Location' => 'https://auth.example.com/final']),
        psr18Response(200, 'ok')
    );
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = newPsr18Client($stub, $transport);

    $response = $client->sendRequest('GET', 'https://auth.example.com/start', [], null);

    expect($response->statusCode)->toBe(200);
});

test('psr18 https to http downgrade refuses body replay on 307', function (): void {
    $stub = new StubPsr18Client(
        psr18Response(307, '', ['Location' => 'http://insecure.example.com/sink'])
    );
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = newPsr18Client($stub, $transport);

    expect(fn (): mixed => $client->sendRequest('POST', 'https://api.example.com/secret', [], 'sensitive=payload'))
        ->toThrow(ApiException::class);
});

test('psr18 N1 302 https to http downgrade with body proceeds as get', function (): void {
    $stub = new StubPsr18Client(
        psr18Response(302, '', ['Location' => 'http://insecure.example.com/final']),
        psr18Response(200, 'ok')
    );
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = newPsr18Client($stub, $transport);

    $response = $client->sendRequest('POST', 'https://api.example.com/start', [], 'sensitive=payload');

    expect($response->statusCode)->toBe(200);
    expect($stub->requests[1]->getMethod())->toBe('GET');
});

test('psr18 exceeding max redirects throws', function (): void {
    $loop = [];
    for ($i = 0; $i < 10; $i++) {
        $loop[] = psr18Response(302, '', ['Location' => 'https://api.example.com/next/' . $i]);
    }
    $transport = TransportOptions::builder()->followRedirects(true)->maxRedirects(3)->build();
    $client = newPsr18Client(new StubPsr18Client(...$loop), $transport);

    expect(fn (): mixed => $client->sendRequest('GET', 'https://api.example.com/start', [], null))
        ->toThrow(ApiException::class);
});

test('psr18 redirect to non http scheme throws', function (): void {
    $stub = new StubPsr18Client(
        psr18Response(302, '', ['Location' => 'file:///etc/passwd'])
    );
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = newPsr18Client($stub, $transport);

    expect(fn (): mixed => $client->sendRequest('GET', 'https://api.example.com/start', [], null))
        ->toThrow(ApiException::class);
});

test('psr18 send after close throws api exception', function (): void {
    $stub = new StubPsr18Client(psr18Response(200, 'ok'));
    $client = newPsr18Client($stub);
    $client->close();

    expect(fn (): mixed => $client->sendRequest('GET', 'http://example.com/after-close', [], null))
        ->toThrow(ApiException::class);
});

test('psr18 decompresses a valid gzip-encoded response body', function (): void {
    $payload = '{"ok":true}';
    $gzipped = gzencode($payload);
    expect($gzipped)->not->toBeFalse();
    $stub = new StubPsr18Client(
        psr18Response(200, (string) $gzipped, [
            'Content-Type' => 'application/json',
            'Content-Encoding' => 'gzip',
        ])
    );
    $client = newPsr18Client($stub);

    $response = $client->sendRequest('GET', 'http://example.com/echo', [], null);

    expect($response->body)->toBe($payload);
});

test('psr18 AL content-encoding gzip lie with plaintext body surfaces ApiException', function (): void {
    $stub = new StubPsr18Client(
        psr18Response(200, 'plain, not gzip', [
            'Content-Type' => 'application/json',
            'Content-Encoding' => 'gzip',
        ])
    );
    $client = newPsr18Client($stub);

    expect(fn (): mixed => $client->sendRequest('GET', 'http://example.com/lie', [], null))
        ->toThrow(ApiException::class);
});

test('psr18 multipart png file gets image png content type', function (): void {
    $tmp = tempnam(sys_get_temp_dir(), 'mp_');
    expect($tmp)->toBeString();
    $pngPath = $tmp . '.png';
    rename($tmp, $pngPath);
    file_put_contents($pngPath, "\x89PNG\r\n\x1A\n");

    try {
        $stub = new StubPsr18Client(psr18Response(200, '{}'));
        $client = newPsr18Client($stub);
        $client->sendRequest(
            'POST',
            'http://example.com/upload',
            [],
            ['file' => new \SplFileObject($pngPath)]
        );

        $sent = (string) $stub->requests[0]->getBody();
        expect($sent)->toContain('Content-Type: image/png');
        expect($stub->requests[0]->getHeaderLine('Content-Type'))->toStartWith('multipart/form-data; boundary=');
    } finally {
        @unlink($pngPath);
    }
});

test('psr18 multipart raw bytes part reuses field name as filename with octet stream', function (): void {
    /* A raw-bytes part with no explicit filename must reuse the field NAME as
     * the filename and emit a Content-Type guessed from that filename's
     * extension, falling back to application/octet-stream when there is none.
     * For a field named "file" (no extension) the wire bytes must therefore
     * carry name="file"; filename="file" AND Content-Type: application/octet-stream. */
    $stream = fopen('php://temp', 'w+');
    expect($stream)->toBeResource();
    fwrite($stream, "\x00\x01\x02");
    rewind($stream);

    $stub = new StubPsr18Client(psr18Response(200, '{}'));
    $client = newPsr18Client($stub);
    $client->sendRequest(
        'POST',
        'http://example.com/upload',
        [],
        ['file' => $stream]
    );

    $sent = (string) $stub->requests[0]->getBody();
    expect($sent)->toContain('Content-Disposition: form-data; name="file"; filename="file"');
    expect($sent)->toContain('Content-Type: application/octet-stream');
});

test('psr18 multipart non ascii field name preserved as utf 8', function (): void {
    $fieldName = 'caféMénù';
    $stub = new StubPsr18Client(psr18Response(200, '{}'));
    $client = newPsr18Client($stub);

    $client->sendRequest('POST', 'http://example.com/upload', [], [$fieldName => 'value']);

    $sent = (string) $stub->requests[0]->getBody();
    expect($sent)->toContain('name="' . $fieldName . '"');
});

/*
 * Cross-language multipart parity: a multipart/form-data body that carries a
 * MODEL part must serialize that part through the SDK's configured
 * ObjectSerializer, so the JSON uses the WIRE property names ("isPrimary",
 * "takenAt" — never the snake_case "is_primary"/"taken_at") and the SDK's
 * RFC 3339 date-time format. The Psr18ApiClient's in-memory encodeMultipart()
 * routes object parts through ObjectSerializer::serialize(); this exercises
 * that exact path and asserts the emitted part JSON honours the SerializedName
 * mapping and the date-time wire format (sub-second precision preserved, e.g.
 * ...05.123+00:00).
 */
test('psr18 multipart model part uses configured serializer wire names', function (): void {
    $metadata = new MultipartModelPart(
        isPrimary: true,
        takenAt: new \DateTime('2020-01-02T03:04:05.123Z'),
    );

    $stub = new StubPsr18Client(psr18Response(200, '[]'));
    $client = newPsr18Client($stub);
    $client->sendRequest(
        'POST',
        'http://example.com/resources/1/uploads',
        [],
        ['metadata' => $metadata]
    );

    $sent = (string) $stub->requests[0]->getBody();

    /* The model part is a JSON part. */
    expect($sent)->toContain('Content-Disposition: form-data; name="metadata"');
    expect($sent)->toContain('Content-Type: application/json');

    /* Wire (camelCase) property names from the model's SerializedName mapping,
     * NOT the snake_case form. */
    expect($sent)->toContain('"isPrimary":true');
    expect($sent)->toContain('"takenAt":');
    expect($sent)->not->toContain('is_primary');
    expect($sent)->not->toContain('taken_at');

    /* The date-time carries the SDK's RFC 3339 wire format with sub-second
     * precision (DateTime::RFC3339_EXTENDED). */
    expect($sent)->toContain('"takenAt":"2020-01-02T03:04:05.123+00:00"');
});
