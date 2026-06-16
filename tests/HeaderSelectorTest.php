<?php

declare(strict_types=1);

use Zitadel\Client\HeaderSelector;

beforeEach(function (): void {
    $this->headerSelector = new HeaderSelector();
});

// isJsonMime tests

test('should return true for application json', function (): void {
    expect($this->headerSelector->isJsonMime('application/json'))->toBeTrue();
});

test('should return true for application json with charset', function (): void {
    expect($this->headerSelector->isJsonMime('application/json; charset=UTF-8'))->toBeTrue();
});

test('should return true for uppercase application json', function (): void {
    expect($this->headerSelector->isJsonMime('APPLICATION/JSON'))->toBeTrue();
});

test('should return true for vendor json types', function (): void {
    expect($this->headerSelector->isJsonMime('application/vnd.api+json'))->toBeTrue();
    expect($this->headerSelector->isJsonMime('application/vnd.company+json'))->toBeTrue();
    expect($this->headerSelector->isJsonMime('application/hal+json'))->toBeTrue();
});

test('should return false for text html', function (): void {
    expect($this->headerSelector->isJsonMime('text/html'))->toBeFalse();
});

test('should return false for application xml', function (): void {
    expect($this->headerSelector->isJsonMime('application/xml'))->toBeFalse();
});

test('should return false for application octet stream', function (): void {
    expect($this->headerSelector->isJsonMime('application/octet-stream'))->toBeFalse();
});

test('should return false for empty string', function (): void {
    expect($this->headerSelector->isJsonMime(''))->toBeFalse();
});

// selectHeaders tests

test('should set accept header when accepts provided', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['application/json'],
        'application/json',
        false
    );
    expect($headers['Accept'])->toEqual('application/json');
});

test('should not set accept header when accepts empty', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        [],
        'application/json',
        false
    );
    expect($headers)->not->toHaveKey('Accept');
});

test('should set content type header when not multipart', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['application/json'],
        'application/json',
        false
    );
    expect($headers['Content-Type'])->toEqual('application/json');
});

test('should not set content type header when multipart', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['application/json'],
        'application/json',
        true
    );
    expect($headers)->not->toHaveKey('Content-Type');
});

test('should default content type to application json when empty', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['application/json'],
        '',
        false
    );
    expect($headers['Content-Type'])->toEqual('application/json');
});

// selectAcceptHeader tests (via selectHeaders)

test('should return single accept as is', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['application/json'],
        'application/json',
        false
    );
    expect($headers['Accept'])->toEqual('application/json');
});

test('should return single non json accept as is', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['text/html'],
        'application/json',
        false
    );
    expect($headers['Accept'])->toEqual('text/html');
});

test('should join multiple types in declaration order', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['image/jpeg', 'image/png', 'application/json'],
        'application/json',
        false
    );
    expect($headers['Accept'])->toEqual('image/jpeg, image/png, application/json');
});

test('should not reorder json types ahead of others', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['text/html', 'application/vnd.api+json', 'application/json'],
        'application/json',
        false
    );
    expect($headers['Accept'])->toEqual('text/html, application/vnd.api+json, application/json');
});

test('should not add quality weights', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['text/html', 'application/json'],
        'application/json',
        false
    );
    expect($headers['Accept'])->toEqual('text/html, application/json');
    expect($headers['Accept'])->not->toContain(';q=');
});

test('should join two non json types in order', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['text/html', 'text/plain'],
        'application/json',
        false
    );
    expect($headers['Accept'])->toEqual('text/html, text/plain');
});

test('should filter out empty entries and join the rest', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['', 'image/jpeg', '', 'image/png'],
        'application/json',
        false
    );
    expect($headers['Accept'])->toEqual('image/jpeg, image/png');
});

test('should filter to a single entry', function (): void {
    $headers = $this->headerSelector->selectHeaders(
        ['', 'application/json'],
        'application/json',
        false
    );
    expect($headers['Accept'])->toEqual('application/json');
});
