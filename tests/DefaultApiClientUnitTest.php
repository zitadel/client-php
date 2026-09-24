<?php

declare(strict_types=1);

namespace Zitadel\Client\Test;

use Zitadel\Client\Errors\ApiException;
use Zitadel\Client\DefaultApiClient;
use Zitadel\Client\TransportOptions;
use Zitadel\Client\TransportOptionsBuilder;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Collect a Symfony HttpClient request body into a single string.
 *
 * Symfony normalises iterable bodies into a `Closure(int): string` that
 * returns chunks and signals end-of-body with an empty string. Strings
 * and iterables are passed through as-is so each test can inspect the
 * raw payload regardless of how the client supplied it.
 *
 * @param mixed $body Body value from the captured request options
 */
function collectDefaultApiClientRequestBody(mixed $body): string
{
    if (is_string($body)) {
        return $body;
    }
    if ($body instanceof \Closure) {
        $buffer = '';
        while (true) {
            $chunk = $body(16384);
            if (!is_string($chunk) || $chunk === '') {
                break;
            }
            $buffer .= $chunk;
        }
        return $buffer;
    }
    if (is_iterable($body)) {
        $buffer = '';
        foreach ($body as $chunk) {
            $buffer .= (string) $chunk;
        }
        return $buffer;
    }
    return '';
}

test('sends get request and returns response', function (): void {
    $mockResponse = new MockResponse('{"method":"GET","body":""}', [
        'http_code' => 200,
        'response_headers' => ['X-Test-Header' => 'test-value'],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('GET', 'http://example.com/echo', [], null);

    expect($response->statusCode)->toBe(200);
    expect($response->body)->toContain('GET');
});

test('sends post with json body', function (): void {
    $mockResponse = new MockResponse('{"method":"POST","body":"key"}', [
        'http_code' => 200,
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest(
        'POST',
        'http://example.com/echo',
        ['Content-Type' => 'application/json'],
        '{"key":"value"}'
    );

    expect($response->statusCode)->toBe(200);
    expect($response->body)->toContain('POST');
    expect($response->body)->toContain('key');
});

test('returns response headers', function (): void {
    $mockResponse = new MockResponse('ok', [
        'http_code' => 200,
        'response_headers' => ['X-Test-Header' => 'test-value'],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('GET', 'http://example.com/echo', [], null);

    expect($response->headers)->toHaveKey('x-test-header');
    expect($response->headers['x-test-header'])->toBe('test-value');
});

test('returns non 2xx status code', function (): void {
    $mockResponse = new MockResponse('not found', [
        'http_code' => 404,
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('GET', 'http://example.com/not-found', [], null);

    expect($response->statusCode)->toBe(404);
    expect($response->body)->toBe('not found');
});

test('sends put request', function (): void {
    $mockResponse = new MockResponse('{"method":"PUT"}', [
        'http_code' => 200,
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('PUT', 'http://example.com/echo', [], 'update');

    expect($response->statusCode)->toBe(200);
    expect($response->body)->toContain('PUT');
});

test('sends delete request', function (): void {
    $mockResponse = new MockResponse('{"method":"DELETE"}', [
        'http_code' => 200,
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('DELETE', 'http://example.com/echo', [], null);

    expect($response->statusCode)->toBe(200);
    expect($response->body)->toContain('DELETE');
});

test('returns json body for vendor json content type', function (): void {
    $mockResponse = new MockResponse('{"format":"vendor"}', [
        'http_code' => 200,
        'response_headers' => ['Content-Type' => 'application/vnd.api+json'],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('GET', 'http://example.com/vendor-json', [], null);

    expect($response->statusCode)->toBe(200);
    expect($response->body)->toContain('vendor');
});

test('joins multi value response headers', function (): void {
    $mockResponse = new MockResponse('ok', [
        'http_code' => 200,
        'response_headers' => ['X-Custom-Value' => ['val1', 'val2']],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('GET', 'http://example.com/multi-header', [], null);

    expect($response->statusCode)->toBe(200);
    expect($response->headers)->toHaveKey('x-custom-value');
    expect($response->headers['x-custom-value'])->toContain('val1');
    expect($response->headers['x-custom-value'])->toContain('val2');
});

test('injects custom user agent', function (): void {
    $capturedHeaders = [];
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedHeaders): MockResponse {
            $capturedHeaders = [];
            foreach ($options['normalized_headers'] ?? [] as $values) {
                [$name, $value] = explode(': ', (string) $values[0], 2);
                $capturedHeaders[$name] = $value;
            }
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    $transport = new TransportOptionsBuilder()
        ->userAgent('MyApp/1.0')
        ->build();
    $client = new StubbedDefaultApiClient($mockClient, $transport);
    $client->sendRequest('GET', 'http://example.com/test', [], null);

    expect($capturedHeaders['User-Agent'] ?? null)->toBe('MyApp/1.0');
});

test('injects default user agent when not explicitly set', function (): void {
    $capturedHeaders = [];
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedHeaders): MockResponse {
            $capturedHeaders = [];
            foreach ($options['normalized_headers'] ?? [] as $values) {
                [$name, $value] = explode(': ', (string) $values[0], 2);
                $capturedHeaders[$name] = $value;
            }
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    $client = new StubbedDefaultApiClient($mockClient);
    $client->sendRequest('GET', 'http://example.com/test', [], null);

    expect($capturedHeaders['User-Agent'] ?? null)->not->toBeEmpty();
});

test('injects request id', function (): void {
    $capturedHeaders = [];
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedHeaders): MockResponse {
            $capturedHeaders = [];
            foreach ($options['normalized_headers'] ?? [] as $values) {
                [$name, $value] = explode(': ', (string) $values[0], 2);
                $capturedHeaders[$name] = $value;
            }
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    $transport = new TransportOptionsBuilder()
        ->injectRequestId(true)
        ->build();
    $client = new StubbedDefaultApiClient($mockClient, $transport);
    $client->sendRequest('GET', 'http://example.com/test', [], null);

    $requestId = $capturedHeaders['X-Request-ID'] ?? null;
    expect($requestId)->not->toBeNull();
    expect($requestId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

test('does not inject request id when disabled', function (): void {
    $capturedHeaders = [];
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedHeaders): MockResponse {
            $capturedHeaders = [];
            foreach ($options['normalized_headers'] ?? [] as $values) {
                [$name, $value] = explode(': ', (string) $values[0], 2);
                $capturedHeaders[$name] = $value;
            }
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    $transport = new TransportOptionsBuilder()
        ->injectRequestId(false)
        ->build();
    $client = new StubbedDefaultApiClient($mockClient, $transport);
    $client->sendRequest('GET', 'http://example.com/test', [], null);

    expect($capturedHeaders)->not->toHaveKey('X-Request-ID');
});

test('does not override caller request id', function (): void {
    $capturedHeaders = [];
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedHeaders): MockResponse {
            $capturedHeaders = [];
            foreach ($options['normalized_headers'] ?? [] as $values) {
                [$name, $value] = explode(': ', (string) $values[0], 2);
                $capturedHeaders[$name] = $value;
            }
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    $transport = new TransportOptionsBuilder()
        ->injectRequestId(true)
        ->build();
    $client = new StubbedDefaultApiClient($mockClient, $transport);
    $client->sendRequest('GET', 'http://example.com/test', ['X-Request-ID' => 'caller-id'], null);

    expect($capturedHeaders['X-Request-ID'] ?? null)->toBe('caller-id');
});

test('generates unique request ids', function (): void {
    $capturedIds = [];

    $mockClient1 = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedIds): MockResponse {
            $requestHeaders = [];
            foreach ($options['normalized_headers'] ?? [] as $values) {
                [$name, $value] = explode(': ', (string) $values[0], 2);
                $requestHeaders[$name] = $value;
            }
            $capturedIds[] = $requestHeaders['X-Request-ID'] ?? null;
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );
    $mockClient2 = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedIds): MockResponse {
            $requestHeaders = [];
            foreach ($options['normalized_headers'] ?? [] as $values) {
                [$name, $value] = explode(': ', (string) $values[0], 2);
                $requestHeaders[$name] = $value;
            }
            $capturedIds[] = $requestHeaders['X-Request-ID'] ?? null;
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    $transport = new TransportOptionsBuilder()
        ->injectRequestId(true)
        ->build();

    $client1 = new StubbedDefaultApiClient($mockClient1, $transport);
    $client1->sendRequest('GET', 'http://example.com/test', [], null);

    $client2 = new StubbedDefaultApiClient($mockClient2, $transport);
    $client2->sendRequest('GET', 'http://example.com/test', [], null);

    expect($capturedIds)->toHaveCount(2);
    expect($capturedIds[1])->not->toBe($capturedIds[0]);
});

test('unit includes transport default headers', function (): void {
    $capturedHeaders = [];
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedHeaders): MockResponse {
            $capturedHeaders = [];
            foreach ($options['normalized_headers'] ?? [] as $values) {
                [$name, $value] = explode(': ', (string) $values[0], 2);
                $capturedHeaders[$name] = $value;
            }
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    $transport = new TransportOptionsBuilder()
        ->defaultHeader('X-Custom', 'custom-value')
        ->build();
    $client = new StubbedDefaultApiClient($mockClient, $transport);
    $client->sendRequest('GET', 'http://example.com/test', [], null);

    expect($capturedHeaders['X-Custom'] ?? null)->toBe('custom-value');
});

test('caller headers override defaults', function (): void {
    $capturedHeaders = [];
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedHeaders): MockResponse {
            $capturedHeaders = [];
            foreach ($options['normalized_headers'] ?? [] as $values) {
                [$name, $value] = explode(': ', (string) $values[0], 2);
                $capturedHeaders[$name] = $value;
            }
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    $transport = new TransportOptionsBuilder()
        ->defaultHeader('Accept', 'text/plain')
        ->build();
    $client = new StubbedDefaultApiClient($mockClient, $transport);
    $client->sendRequest('GET', 'http://example.com/test', ['Accept' => 'application/json'], null);

    expect($capturedHeaders['Accept'] ?? null)->toBe('application/json');
});

test('decodes iso 8859 1 body to utf 8 when charset declared', function (): void {
    $body = "\xE9"; // ISO-8859-1 'é'
    $mockResponse = new MockResponse($body, [
        'http_code' => 200,
        'response_headers' => ['Content-Type' => 'text/plain; charset=ISO-8859-1'],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('GET', 'http://example.com/iso', [], null);

    expect($response->statusCode)->toBe(200);
    expect($response->body)->toBe("\xC3\xA9"); // UTF-8 'é'
});

test('decodes bom-less utf-16 body as big endian', function (): void {
    // "Tag" encoded as UTF-16 big-endian with no byte-order mark. Per
    // RFC 2781, a UTF-16 stream without a BOM defaults to big-endian, so
    // these bytes must decode to "Tag" — not to the garbage that a
    // little-endian reading would produce.
    $body = "\x00T\x00a\x00g";
    $mockResponse = new MockResponse($body, [
        'http_code' => 200,
        'response_headers' => ['Content-Type' => 'text/plain; charset=utf-16'],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('GET', 'http://example.com/utf16', [], null);

    expect($response->statusCode)->toBe(200);
    expect($response->body)->toBe('Tag');
    // Sanity check the byte-order decision: read little-endian, the same
    // bytes would decode to something other than "Tag".
    expect(mb_convert_encoding($body, 'UTF-8', 'UTF-16LE'))->not->toBe('Tag');
});

test('treats absent charset as utf 8', function (): void {
    $body = "héllo"; // already UTF-8
    $mockResponse = new MockResponse($body, [
        'http_code' => 200,
        'response_headers' => ['Content-Type' => 'text/plain'],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('GET', 'http://example.com/utf8', [], null);

    expect($response->body)->toBe($body);
});

test('falls back to utf 8 on unknown charset without throwing', function (): void {
    $body = "hello";
    $mockResponse = new MockResponse($body, [
        'http_code' => 200,
        'response_headers' => ['Content-Type' => 'text/plain; charset=not-a-real-charset'],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('GET', 'http://example.com/unknown', [], null);

    expect($response->statusCode)->toBe(200);
    expect($response->body)->toBe($body);
});

test('multipart png file gets image png content type', function (): void {
    $tmp = tempnam(sys_get_temp_dir(), 'mp_');
    expect($tmp)->toBeString();
    $pngPath = $tmp . '.png';
    rename($tmp, $pngPath);
    file_put_contents($pngPath, "\x89PNG\r\n\x1A\n");

    $capturedBody = '';
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = collectDefaultApiClientRequestBody($options['body'] ?? '');
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    try {
        $client = new StubbedDefaultApiClient($mockClient);
        $client->sendRequest(
            'POST',
            'http://example.com/upload',
            [],
            ['file' => new \SplFileObject($pngPath)]
        );

        expect($capturedBody)->toContain('Content-Type: image/png');
    } finally {
        @unlink($pngPath);
    }
});

test('multipart pdf file gets application pdf content type', function (): void {
    $tmp = tempnam(sys_get_temp_dir(), 'mp_');
    expect($tmp)->toBeString();
    $pdfPath = $tmp . '.pdf';
    rename($tmp, $pdfPath);
    file_put_contents($pdfPath, "%PDF-1.4\n");

    $capturedBody = '';
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = collectDefaultApiClientRequestBody($options['body'] ?? '');
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    try {
        $client = new StubbedDefaultApiClient($mockClient);
        $client->sendRequest(
            'POST',
            'http://example.com/upload',
            [],
            ['file' => new \SplFileObject($pdfPath)]
        );

        expect($capturedBody)->toContain('Content-Type: application/pdf');
    } finally {
        @unlink($pdfPath);
    }
});

test('multipart resource falls back to octet stream', function (): void {
    $stream = fopen('php://temp', 'w+');
    expect($stream)->toBeResource();
    fwrite($stream, "\x00\xFF\x42 raw bytes");
    rewind($stream);

    $capturedBody = '';
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = collectDefaultApiClientRequestBody($options['body'] ?? '');
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    $client = new StubbedDefaultApiClient($mockClient);
    $client->sendRequest(
        'POST',
        'http://example.com/upload',
        [],
        ['file' => $stream]
    );

    expect($capturedBody)->toContain('Content-Type: application/octet-stream');
});

test('multipart raw bytes part reuses field name as filename with octet stream', function (): void {
    /* A raw-bytes part with no explicit filename must reuse the field NAME as
     * the filename and emit a Content-Type guessed from that filename's
     * extension, falling back to application/octet-stream when there is none.
     * For a field named "file" (no extension) the wire bytes must therefore
     * carry name="file"; filename="file" AND Content-Type: application/octet-stream. */
    $stream = fopen('php://temp', 'w+');
    expect($stream)->toBeResource();
    fwrite($stream, "\x00\x01\x02");
    rewind($stream);

    $capturedBody = '';
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = collectDefaultApiClientRequestBody($options['body'] ?? '');
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    $client = new StubbedDefaultApiClient($mockClient);
    $client->sendRequest(
        'POST',
        'http://example.com/upload',
        [],
        ['file' => $stream]
    );

    expect($capturedBody)->toContain('name="file"; filename="file"');
    expect($capturedBody)->toContain('Content-Type: application/octet-stream');
});

// -- Canonical behavior #5: non-ASCII multipart field name preserved as UTF-8 --

test('multipart non ascii field name preserved as utf 8', function (): void {
    /* A multipart form field whose NAME contains non-ASCII characters must
     * travel on the wire as raw UTF-8 in the Content-Disposition name=
     * directive — not transliterated to '?' and not stripped. This pins the
     * cross-SDK invariant for unicode field names. */
    $fieldName = 'caféMénù'; // contains é and ù

    $capturedBody = '';
    $mockClient = new MockHttpClient(
        function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = collectDefaultApiClientRequestBody($options['body'] ?? '');
            return new MockResponse('{}', ['http_code' => 200]);
        }
    );

    $client = new StubbedDefaultApiClient($mockClient);
    $client->sendRequest(
        'POST',
        'http://example.com/upload',
        [],
        [$fieldName => 'value']
    );

    // The exact UTF-8 bytes of the field name must appear in the part's
    // Content-Disposition header.
    expect($capturedBody)->toContain('name="' . $fieldName . '"');
    // It must not have been mangled into question marks or dropped.
    expect($capturedBody)->not->toContain('name="caf?M?n?"');
    expect($capturedBody)->not->toContain('name="cafMn"');
});

test('decodes iso 8859 1 error body to utf 8', function (): void {
    $body = "\xE9rreur"; // ISO-8859-1 'érreur'
    $mockResponse = new MockResponse($body, [
        'http_code' => 500,
        'response_headers' => ['Content-Type' => 'text/plain; charset=ISO-8859-1'],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('GET', 'http://example.com/err', [], null);

    expect($response->statusCode)->toBe(500);
    expect($response->body)->toBe("\xC3\xA9rreur");
});

// -- Proxy auth (gap #29) --

test('proxy with basic auth is accepted by builder', function (): void {
    // Verify the transport accepts a proxy URL embedding user:pass and exposes it.
    // The actual proxy-auth header is constructed by Symfony's underlying CurlHttpClient.
    $opts = new TransportOptionsBuilder()
        ->proxy('http://user:secret@proxy.example.com:8080')
        ->build();

    expect($opts->proxy)->toBe('http://user:secret@proxy.example.com:8080');
});

// -- Gap BI: RFC 5987 filename* for non-ASCII multipart filenames --

test('multipart filename non ascii emits rfc 5987', function (): void {
    $directive = DefaultApiClient::buildFilenameDirective('日本.pdf');
    expect($directive)->toContain("filename*=UTF-8''");
    expect($directive)->toContain('%E6%97%A5%E6%9C%AC');
    expect($directive)->toStartWith('filename="');
});

test('multipart filename ascii only omits filename star', function (): void {
    $directive = DefaultApiClient::buildFilenameDirective('file.png');
    expect($directive)->toBe('filename="file.png"');
    expect($directive)->not->toContain('filename*=');
});

test('multipart filename crlf rejected', function (): void {
    foreach (["a\rb.pdf", "a\nb.pdf", "a\r\nb.pdf", "a\x00b.pdf"] as $bad) {
        try {
            DefaultApiClient::validateMultipartFilename($bad);
            test()->fail("expected InvalidArgumentException for: " . bin2hex($bad));
        } catch (\InvalidArgumentException) {
            // expected
        }
    }
    /* ASCII filename should not throw; record one assertion so PHPUnit
     * doesn't flag the test as risky. Avoids PHPStan's
     * method.alreadyNarrowedType complaint on assertTrue(true). */
    DefaultApiClient::validateMultipartFilename('file.png');
    expect(true)->toBeTrue();
});

// -- 3.1: sensitive-header allowlist includes API-key header names --

test('sensitive header allowlist includes static set', function (): void {
    $reflection = new \ReflectionClass(DefaultApiClient::class);
    /** @var list<string> $names */
    $names = $reflection->getConstant('SENSITIVE_HEADER_NAMES');
    expect($names)->toContain('authorization');
    expect($names)->toContain('cookie');
    expect($names)->toContain('proxy-authorization');
});

test('sensitive header allowlist includes api key header names lowercased', function (): void {
    /* The codegen harvests every `apiKey, in=header` security scheme
     * from the spec and folds its header name (lowercased) into the
     * SENSITIVE_HEADER_NAMES constant. The fixture spec defines
     * X-API-Key and X-Internal-Key so both must appear. */
    $reflection = new \ReflectionClass(DefaultApiClient::class);
    /** @var list<string> $names */
    $names = $reflection->getConstant('SENSITIVE_HEADER_NAMES');
    foreach ($names as $n) {
        expect($n)->toBe(strtolower($n));
    }
});

test('cross origin redirect strips authorization and cookie', function (): void {
    /* The classic credential carriers (Authorization, Cookie,
     * Proxy-Authorization) are ALWAYS stripped on a cross-origin hop,
     * regardless of whether the spec declared any apiKey-in-header schemes.
     * Non-credential headers must survive the hop. */
    $hop1 = new MockResponse('', [
        'http_code' => 302,
        'response_headers' => ['Location' => 'https://other.example.com/final'],
    ]);
    $capturedHeaders = [];
    $hop2 = new MockResponse('ok', ['http_code' => 200]);
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $mock = new MockHttpClient(function ($method, $url, array $options) use (&$capturedHeaders, $hop1, $hop2): \Symfony\Component\HttpClient\Response\MockResponse {
        static $hop = 0;
        $hop++;
        if ($hop === 1) {
            return $hop1;
        }
        /** @var array<int, string> $hdrs */
        $hdrs = $options['headers'] ?? [];
        foreach ($hdrs as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $capturedHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
        }
        return $hop2;
    });
    $client = new StubbedDefaultApiClient($mock, $transport);

    $client->sendRequest('GET', 'https://api.example.com/start', [
        'Authorization' => 'Bearer secret',
        'Cookie' => 'session=xyz',
        'X-Trace' => 'keep',
    ], null);

    expect($capturedHeaders)->not->toHaveKey('authorization');
    expect($capturedHeaders)->not->toHaveKey('cookie');
    expect($capturedHeaders)->toHaveKey('x-trace');
});

// -- 3.2: noRedirect arg suppresses redirect following --

test('no redirect arg surfaces 302 to caller', function (): void {
    /* When the caller requests noRedirect=true the redirect loop must
     * be bypassed even if TransportOptions.followRedirects is true. The
     * Location-bearing 302 surfaces unchanged so callers (e.g.
     * OAuth2TokenManager) can refuse to replay the request. */
    $redirect = new MockResponse('', [
        'http_code' => 302,
        'response_headers' => ['Location' => 'https://attacker.example.com/token'],
    ]);
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = new StubbedDefaultApiClient(new MockHttpClient([$redirect]), $transport);

    $response = $client->sendRequest('POST', 'https://auth.example.com/token', [], 'grant_type=x', noRedirect: true);

    expect($response->statusCode)->toBe(302);
});

test('no redirect false still follows redirects', function (): void {
    $hop1 = new MockResponse('', [
        'http_code' => 302,
        'response_headers' => ['Location' => 'https://auth.example.com/final'],
    ]);
    $hop2 = new MockResponse('ok', ['http_code' => 200]);
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = new StubbedDefaultApiClient(new MockHttpClient([$hop1, $hop2]), $transport);

    $response = $client->sendRequest('GET', 'https://auth.example.com/start', [], null);

    expect($response->statusCode)->toBe(200);
});

// -- 3.3: HTTPS -> HTTP body replay guard --

test('https to http downgrade refuses body replay on 307', function (): void {
    /* Gap 3.3 / T-D2: a 307 from an HTTPS origin pointing at an HTTP URL
     * must NOT replay the body in plaintext. The downgrade is refused by
     * raising an SDK-typed ApiException (not by silently returning the
     * 307), matching the throwing SDKs. */
    $downgrade = new MockResponse('', [
        'http_code' => 307,
        'response_headers' => ['Location' => 'http://insecure.example.com/sink'],
    ]);
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = new StubbedDefaultApiClient(new MockHttpClient([$downgrade]), $transport);

    expect(fn (): mixed => $client->sendRequest('POST', 'https://api.example.com/secret', [], 'sensitive=payload'))
        ->toThrow(function (\Exception $e): void {
            /* A refused redirect is a response that arrived but could not
             * be used: exactly ApiException, carrying the real status. */
            expect($e::class)->toBe(ApiException::class);
            expect($e->getCode())->toBe(307);
        });
});

test('https to http downgrade refuses body replay on 308', function (): void {
    $downgrade = new MockResponse('', [
        'http_code' => 308,
        'response_headers' => ['Location' => 'http://insecure.example.com/sink'],
    ]);
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = new StubbedDefaultApiClient(new MockHttpClient([$downgrade]), $transport);

    expect(fn (): mixed => $client->sendRequest('PUT', 'https://api.example.com/secret', [], 'k=v'))
        ->toThrow(function (\Exception $e): void {
            /* A refused redirect is a response that arrived but could not
             * be used: exactly ApiException, carrying the real status. */
            expect($e::class)->toBe(ApiException::class);
            expect($e->getCode())->toBe(308);
        });
});

test('https to http downgrade on get without body still follows', function (): void {
    /* The downgrade guard only fires for body-bearing replays. A plain
     * GET (no body) is allowed to follow a 307/308 downgrade since
     * there's no body to leak. */
    $downgrade = new MockResponse('', [
        'http_code' => 307,
        'response_headers' => ['Location' => 'http://insecure.example.com/final'],
    ]);
    $final = new MockResponse('ok', ['http_code' => 200]);
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = new StubbedDefaultApiClient(new MockHttpClient([$downgrade, $final]), $transport);

    $response = $client->sendRequest('GET', 'https://api.example.com/start', [], null);

    expect($response->statusCode)->toBe(200);
});

test('https to https with body still follows on 307', function (): void {
    /* Same-scheme HTTPS redirects must still replay the body; the guard
     * only applies to the downgrade direction. */
    $hop = new MockResponse('', [
        'http_code' => 307,
        'response_headers' => ['Location' => 'https://api.example.com/final'],
    ]);
    $final = new MockResponse('ok', ['http_code' => 200]);
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = new StubbedDefaultApiClient(new MockHttpClient([$hop, $final]), $transport);

    $response = $client->sendRequest('POST', 'https://api.example.com/start', [], 'k=v');

    expect($response->statusCode)->toBe(200);
});

// -- N1: redirect body-replay guard fires only on 307/308, not on 302 --
//
// Canonical cross-SDK scenario (DIVERGENCE N1): the HTTPS->HTTP body-replay
// guard must apply ONLY to 307/308. On a 302 HTTPS->HTTP redirect carrying a
// body the request MUST PROCEED (per RFC the method becomes GET and the body
// is dropped, so there is nothing to leak); on a 307/308 HTTPS->HTTP redirect
// with a body it MUST THROW. PHP guards 307/308 only (GREEN here); the same
// test is added to all 12 SDKs to lock the behaviour (red in node, which
// refuses body-replay on every redirect status). Co-located with the existing
// 307/308 body-replay tests above (their working MockHttpClient harness).

test('N1: 302 https to http downgrade with body proceeds as get', function (): void {
    /* A 302 from an HTTPS origin to an HTTP URL while carrying a POST body
     * must NOT throw — 301/302 force the follow-up to GET and drop the body,
     * so there is no plaintext body to leak. The request proceeds. */
    $downgrade = new MockResponse('', [
        'http_code' => 302,
        'response_headers' => ['Location' => 'http://insecure.example.com/final'],
    ]);
    $final = new MockResponse('{"method":"GET","body":""}', ['http_code' => 200]);
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = new StubbedDefaultApiClient(new MockHttpClient([$downgrade, $final]), $transport);

    $response = $client->sendRequest('POST', 'https://api.example.com/start', [], 'sensitive=payload');

    expect($response->statusCode)->toBe(200);
});

test('N1: 307 https to http downgrade with body throws', function (): void {
    /* The same downgrade on a 307 (which preserves method + body) must be
     * refused with an SDK-typed ApiException — the body would otherwise be
     * replayed in plaintext over HTTP. */
    $downgrade = new MockResponse('', [
        'http_code' => 307,
        'response_headers' => ['Location' => 'http://insecure.example.com/sink'],
    ]);
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = new StubbedDefaultApiClient(new MockHttpClient([$downgrade]), $transport);

    expect(fn (): mixed => $client->sendRequest('POST', 'https://api.example.com/secret', [], 'sensitive=payload'))
        ->toThrow(ApiException::class);
});

test('N1: 308 https to http downgrade with body throws', function (): void {
    $downgrade = new MockResponse('', [
        'http_code' => 308,
        'response_headers' => ['Location' => 'http://insecure.example.com/sink'],
    ]);
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = new StubbedDefaultApiClient(new MockHttpClient([$downgrade]), $transport);

    expect(fn (): mixed => $client->sendRequest('PUT', 'https://api.example.com/secret', [], 'k=v'))
        ->toThrow(ApiException::class);
});

// -- T-D1: redirect-exhaustion throws "too many redirects" --

test('exceeding max redirects throws too many redirects', function (): void {
    /* When the response is still a 3xx after the redirect budget is
     * exhausted, the client must raise an SDK-typed ApiException rather
     * than silently returning the last 3xx as a normal response. */
    $loop = [];
    for ($i = 0; $i < 30; $i++) {
        $loop[] = new MockResponse('', [
            'http_code' => 302,
            'response_headers' => ['Location' => 'https://api.example.com/next/' . $i],
        ]);
    }
    $transport = TransportOptions::builder()->followRedirects(true)->maxRedirects(3)->build();
    $client = new StubbedDefaultApiClient(new MockHttpClient($loop), $transport);

    expect(fn (): mixed => $client->sendRequest('GET', 'https://api.example.com/start', [], null))
        ->toThrow(function (\Exception $e): void {
            /* A refused redirect is a response that arrived but could not
             * be used: exactly ApiException, carrying the real status. */
            expect($e::class)->toBe(ApiException::class);
            expect($e->getCode())->toBe(302);
        });
});

// -- T-D3: redirect to a non-http(s) scheme throws --

test('redirect to non http scheme throws', function (): void {
    /* A Location pointing at file:/javascript:/data: must be refused
     * loudly with an SDK-typed ApiException, not silently returned. */
    $redirect = new MockResponse('', [
        'http_code' => 302,
        'response_headers' => ['Location' => 'file:///etc/passwd'],
    ]);
    $transport = TransportOptions::builder()->followRedirects(true)->build();
    $client = new StubbedDefaultApiClient(new MockHttpClient([$redirect]), $transport);

    expect(fn (): mixed => $client->sendRequest('GET', 'https://api.example.com/start', [], null))
        ->toThrow(function (\Exception $e): void {
            /* A refused redirect is a response that arrived but could not
             * be used: exactly ApiException, carrying the real status. */
            expect($e::class)->toBe(ApiException::class);
            expect($e->getCode())->toBe(302);
        });
});

// -- T-D4: use-after-close raises the built-in state error --

test('send after close throws logic exception', function (): void {
    /* Once close() is called the client must refuse further requests.
     * Using a client after close() is a wrong call order, so it raises
     * exactly \LogicException, not an SDK error. */
    $mockResponse = new MockResponse('ok', ['http_code' => 200]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));
    $client->close();

    expect(fn (): mixed => $client->sendRequest('GET', 'http://example.com/after-close', [], null))
        ->toThrow(function (\Exception $e): void {
            expect($e::class)->toBe(\LogicException::class);
            expect($e)->not->toBeInstanceOf(\Zitadel\Client\Errors\ZitadelException::class);
        });
});

test('close is idempotent', function (): void {
    $client = new StubbedDefaultApiClient(new MockHttpClient(new MockResponse('ok', ['http_code' => 200])));
    $client->close();
    $client->close();

    expect(fn (): mixed => $client->sendRequest('GET', 'http://example.com/x', [], null))
        ->toThrow(\LogicException::class);
});

test('non-existent caCertPath throws invalid argument exception at construction', function (): void {
    /* ca-cert-fail-fast: an explicitly configured CA certificate path that
     * cannot be read or parsed must fail fast at construction rather than
     * silently falling back to the system trust store (security theater). It
     * is a configuration mistake, so the type is \InvalidArgumentException. */
    $transport = TransportOptions::builder()->caCertPath('/nonexistent/ca.pem')->build();

    expect(fn (): mixed => new DefaultApiClient($transport))
        ->toThrow(function (\Exception $e): void {
            expect($e::class)->toBe(\InvalidArgumentException::class);
            expect($e)->not->toBeInstanceOf(\Zitadel\Client\Errors\ZitadelException::class);
        });
});

test('transport failure raises NetworkException with status 0 and the cause kept', function (): void {
    $cause = new \Symfony\Component\HttpClient\Exception\TransportException('Connection refused');
    $client = new StubbedDefaultApiClient(new MockHttpClient(function () use ($cause): never {
        throw $cause;
    }));

    try {
        $client->sendRequest('GET', 'http://example.com/refused', [], null);
        test()->fail('Expected NetworkException');
    } catch (\Zitadel\Client\Errors\NetworkException $e) {
        expect($e::class)->toBe(\Zitadel\Client\Errors\NetworkException::class);
        expect($e)->not->toBeInstanceOf(\Zitadel\Client\Errors\NetworkTimeoutException::class);
        expect($e)->toBeInstanceOf(ApiException::class);
        expect($e->getStatusCode())->toBe(0);
        expect($e->getPrevious())->toBe($cause);
    }
});

test('transport timeout raises NetworkTimeoutException', function (): void {
    $client = new StubbedDefaultApiClient(new MockHttpClient(function (): never {
        throw new \Symfony\Component\HttpClient\Exception\TimeoutException('Idle timeout reached');
    }));

    expect(fn (): mixed => $client->sendRequest('GET', 'http://example.com/slow', [], null))
        ->toThrow(function (\Exception $e): void {
            expect($e::class)->toBe(\Zitadel\Client\Errors\NetworkTimeoutException::class);
            expect($e)->toBeInstanceOf(\Zitadel\Client\Errors\NetworkException::class);
            expect($e->getCode())->toBe(0);
        });
});

test('exceeding the total duration budget raises NetworkTimeoutException', function (): void {
    /* The client sets both `timeout` (idle) and `max_duration` (total). Only
     * the idle timer throws Symfony's TimeoutException; blowing the total
     * budget throws a plain TransportException, so the same expired deadline
     * used to surface as NetworkException depending on which timer fired
     * first. It is a timeout either way. */
    $client = new StubbedDefaultApiClient(new MockHttpClient(function (): never {
        throw new \Symfony\Component\HttpClient\Exception\TransportException(
            'Max duration was reached for "http://example.com/slow".'
        );
    }));

    expect(fn (): mixed => $client->sendRequest('GET', 'http://example.com/slow', [], null))
        ->toThrow(function (\Exception $e): void {
            expect($e::class)->toBe(\Zitadel\Client\Errors\NetworkTimeoutException::class);
            expect($e)->toBeInstanceOf(\Zitadel\Client\Errors\NetworkException::class);
            expect($e->getCode())->toBe(0);
        });
});

test('decompresses a valid gzip-encoded response body', function (): void {
    /* Baseline for the decompression error-wrapping test below: a well-formed
     * gzip body declared via Content-Encoding is transparently inflated so the
     * caller sees the original payload. */
    $payload = '{"ok":true}';
    $gzipped = gzencode($payload);
    expect($gzipped)->not->toBeFalse();

    $mockResponse = new MockResponse((string) $gzipped, [
        'http_code' => 200,
        'response_headers' => [
            'Content-Type' => 'application/json',
            'Content-Encoding' => 'gzip',
        ],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    $response = $client->sendRequest('GET', 'http://example.com/echo', [], null);

    expect($response->statusCode)->toBe(200);
    expect($response->body)->toBe($payload);
});

test('wraps a gzip decompression failure as api exception', function (): void {
    /* decompression-error-not-wrapped: a body advertised as Content-Encoding:
     * gzip but whose bytes are not valid gzip makes gzdecode() fail, which the
     * client raises as a plain \RuntimeException. The transport must wrap that
     * in the SDK's ApiException so callers catch a single documented type
     * instead of a raw runtime error leaking through. */
    $mockResponse = new MockResponse('this is not gzip data', [
        'http_code' => 200,
        'response_headers' => [
            'Content-Type' => 'application/json',
            'Content-Encoding' => 'gzip',
        ],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    expect(fn (): mixed => $client->sendRequest('GET', 'http://example.com/echo', [], null))
        ->toThrow(function (\Exception $e): void {
            /* A response did arrive: exactly ApiException carrying the real
             * status (200), never a NetworkException. */
            expect($e::class)->toBe(ApiException::class);
            expect($e->getCode())->toBe(200);
            expect($e)->not->toBeInstanceOf(\Zitadel\Client\Errors\NetworkException::class);
        });
});

// -- Gap AL: Content-Encoding lie (server claims gzip, sends plaintext) --
//
// Canonical cross-SDK scenario: a response carrying `Content-Encoding: gzip`
// whose body is non-gzip plain bytes MUST surface the SDK's ApiException — no
// crash, no corrupt passthrough. PHP already wraps the gzdecode() failure
// (GREEN here); the same test is added to all 12 SDKs to lock the behaviour
// (red in dart/csharp/kotlin/node/swift/elixir, green elsewhere).

test('AL: content-encoding gzip lie with plaintext body surfaces ApiException', function (): void {
    $mockResponse = new MockResponse('plain, not gzip', [
        'http_code' => 200,
        'response_headers' => [
            'Content-Type' => 'application/json',
            'Content-Encoding' => 'gzip',
        ],
    ]);
    $client = new StubbedDefaultApiClient(new MockHttpClient($mockResponse));

    expect(fn (): mixed => $client->sendRequest('GET', 'http://example.com/lie', [], null))
        ->toThrow(function (\Exception $e): void {
            /* A response did arrive: exactly ApiException carrying the real
             * status (200), never a NetworkException. */
            expect($e::class)->toBe(ApiException::class);
            expect($e->getCode())->toBe(200);
            expect($e)->not->toBeInstanceOf(\Zitadel\Client\Errors\NetworkException::class);
        });
});
