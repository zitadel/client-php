<?php

declare(strict_types=1);

use Zitadel\Client\TransportOptions;

test('verify ssl defaults to true', function (): void {
    $opts = TransportOptions::builder()->build();
    expect($opts->verifySsl)->toBeTrue();
});

test('ca cert path defaults to null', function (): void {
    $opts = TransportOptions::builder()->build();
    expect($opts->caCertPath)->toBeNull();
});

test('proxy defaults to null', function (): void {
    $opts = TransportOptions::builder()->build();
    expect($opts->proxy)->toBeNull();
});

test('timeout defaults to ten seconds', function (): void {
    // Per fix #27: default request timeout is 10000ms (10 seconds).
    $opts = TransportOptions::builder()->build();
    expect($opts->timeout)->toBe(10000);
});

test('timeout can be set to null for no timeout', function (): void {
    $opts = TransportOptions::builder()->timeout(null)->build();
    expect($opts->timeout)->toBeNull();
});

test('follow redirects defaults to true', function (): void {
    $opts = TransportOptions::builder()->build();
    expect($opts->followRedirects)->toBeTrue();
});

test('max redirects defaults to null', function (): void {
    $opts = TransportOptions::builder()->build();
    expect($opts->maxRedirects)->toBeNull();
});

test('user agent defaults to non empty string', function (): void {
    $opts = TransportOptions::builder()->build();
    expect($opts->userAgent)->not->toBeNull();
    expect($opts->userAgent)->not->toBeEmpty();
});

test('default headers defaults to empty', function (): void {
    $opts = TransportOptions::builder()->build();
    expect($opts->defaultHeaders)->toBe([]);
});

test('inject request id defaults to false', function (): void {
    $opts = TransportOptions::builder()->build();
    expect($opts->injectRequestId)->toBeFalse();
});

test('builder sets all fields', function (): void {
    $opts = TransportOptions::builder()
        ->verifySsl(false)
        ->caCertPath('/path/to/ca.pem')
        ->proxy('http://proxy:8080')
        ->timeout(5000)
        ->followRedirects(false)
        ->maxRedirects(3)
        ->userAgent('TestAgent/1.0')
        ->defaultHeader('X-Custom', 'value')
        ->injectRequestId(true)
        ->build();

    expect($opts->verifySsl)->toBeFalse();
    expect($opts->caCertPath)->toBe('/path/to/ca.pem');
    expect($opts->proxy)->toBe('http://proxy:8080');
    expect($opts->timeout)->toBe(5000);
    expect($opts->followRedirects)->toBeFalse();
    expect($opts->maxRedirects)->toBe(3);
    expect($opts->userAgent)->toBe('TestAgent/1.0');
    expect($opts->defaultHeaders)->toBe(['X-Custom' => 'value']);
    expect($opts->injectRequestId)->toBeTrue();
});

test('follow redirects defaults to true with null max redirects', function (): void {
    $opts = TransportOptions::builder()
        ->followRedirects(true)
        ->build();

    expect($opts->followRedirects)->toBeTrue();
    expect($opts->maxRedirects)->toBeNull();
});

test('invalid proxy url throws exception', function (): void {
    expect(fn (): \Zitadel\Client\TransportOptions => TransportOptions::builder()->proxy('not-a-url')->build())
        ->toThrow(\InvalidArgumentException::class);
});

test('null proxy url is accepted', function (): void {
    $opts = TransportOptions::builder()->proxy(null)->build();
    expect($opts->proxy)->toBeNull();
});

test('builder methods return same instance', function (): void {
    $builder = TransportOptions::builder();

    expect($builder->verifySsl(true))->toBe($builder);
    expect($builder->caCertPath(null))->toBe($builder);
    expect($builder->proxy(null))->toBe($builder);
    expect($builder->timeout(null))->toBe($builder);
    expect($builder->followRedirects(true))->toBe($builder);
    expect($builder->maxRedirects(null))->toBe($builder);
    expect($builder->userAgent(null))->toBe($builder);
    expect($builder->defaultHeader('X-Key', 'val'))->toBe($builder);
    expect($builder->defaultHeaders([]))->toBe($builder);
    expect($builder->injectRequestId(false))->toBe($builder);
});

test('accumulates headers from default header calls', function (): void {
    $opts = TransportOptions::builder()
        ->defaultHeader('X-First', 'one')
        ->defaultHeader('X-Second', 'two')
        ->build();

    expect($opts->defaultHeaders)->toHaveCount(2);
    expect($opts->defaultHeaders['X-First'])->toBe('one');
    expect($opts->defaultHeaders['X-Second'])->toBe('two');
});

test('merges headers from default headers call', function (): void {
    $opts = TransportOptions::builder()
        ->defaultHeader('X-First', 'one')
        ->defaultHeaders(['X-Second' => 'two', 'X-Third' => 'three'])
        ->build();

    expect($opts->defaultHeaders)->toHaveCount(3);
    expect($opts->defaultHeaders['X-First'])->toBe('one');
    expect($opts->defaultHeaders['X-Second'])->toBe('two');
    expect($opts->defaultHeaders['X-Third'])->toBe('three');
});

test('modifying source map does not affect built options', function (): void {
    $headers = ['X-Original' => 'original'];

    $opts = TransportOptions::builder()
        ->defaultHeaders($headers)
        ->build();

    // PHP arrays are value types, so modifying $headers after build
    // cannot affect the built object. Verify the object has the original value.
    $headers['X-Added'] = 'added';

    expect($opts->defaultHeaders)->toHaveCount(1);
    expect($opts->defaultHeaders['X-Original'])->toBe('original');
    expect($opts->defaultHeaders)->not->toHaveKey('X-Added');
});

test('builder produces independent instances', function (): void {
    $builder = TransportOptions::builder()->verifySsl(false);
    $first = $builder->build();
    $second = $builder->build();

    expect($second->verifySsl)->toBe($first->verifySsl);
    expect($second)->not->toBe($first);
});

// TimeoutConfigTests

test('setting timeout is accessible', function (): void {
    $opts = TransportOptions::builder()->timeout(5000)->build();
    expect($opts->timeout)->toBe(5000);
});

test('timeout field is named timeout', function (): void {
    // Verify via the property that the field is named 'timeout' (not e.g. 'connectionTimeout').
    $opts = TransportOptions::builder()->timeout(1000)->build();
    expect($opts->timeout)->not->toBeNull();
    expect($opts->timeout)->toBe(1000);
});

// ProxyConfigTests

test('proxy url is preserved on read back', function (): void {
    $opts = TransportOptions::builder()
        ->proxy('http://proxy.example.com:8080')
        ->build();
    expect($opts->proxy)->toBe('http://proxy.example.com:8080');
});

test('setting proxy is supported on all platforms', function (): void {
    // Proxy configuration must not throw on any platform.
    $opts = TransportOptions::builder()
        ->proxy('http://proxy.example.com:8080')
        ->build();
    expect($opts->proxy)->toBe('http://proxy.example.com:8080');
});
