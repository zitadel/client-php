<?php

declare(strict_types=1);

use Zitadel\Client\ApiResult;

test('exposes status code', function (): void {
    $result = new ApiResult(statusCode: 200, data: null, rawBody: '', headers: []);

    expect($result->statusCode)->toBe(200);
});

test('exposes deserialized data', function (): void {
    $data = ['id' => 1, 'name' => 'fido'];
    $result = new ApiResult(statusCode: 200, data: $data, rawBody: '', headers: []);

    expect($result->data)->toBe($data);
});

test('data may be null', function (): void {
    $result = new ApiResult(statusCode: 204, data: null, rawBody: '', headers: []);

    expect($result->data)->toBeNull();
});

test('exposes raw body', function (): void {
    $result = new ApiResult(statusCode: 200, data: null, rawBody: '{"id":1}', headers: []);

    expect($result->rawBody)->toBe('{"id":1}');
});

test('exposes headers', function (): void {
    $headers = ['Content-Type' => 'application/json', 'X-Request-Id' => 'abc'];
    $result = new ApiResult(statusCode: 200, data: null, rawBody: '', headers: $headers);

    expect($result->headers)->toBe($headers);
});

test('carries all fields together', function (): void {
    $result = new ApiResult(
        statusCode: 201,
        data: ['ok' => true],
        rawBody: '{"ok":true}',
        headers: ['Location' => '/pets/1'],
    );

    expect($result->statusCode)->toBe(201);
    expect($result->data)->toBe(['ok' => true]);
    expect($result->rawBody)->toBe('{"ok":true}');
    expect($result->headers)->toBe(['Location' => '/pets/1']);
});
