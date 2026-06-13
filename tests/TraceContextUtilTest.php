<?php

declare(strict_types=1);

use Zitadel\Client\TraceContextUtil;

test('no op without tracer', function (): void {
    $headers = [];
    TraceContextUtil::injectTraceContext($headers);
    expect($headers)->toBeEmpty();
});

test('empty headers do not cause exception', function (): void {
    $headers = [];
    TraceContextUtil::injectTraceContext($headers);
    expect($headers)->toBeEmpty();
});

/**
 * Test that injectTraceContext does not inject traceparent when OTel is not installed.
 */
test('does not inject traceparent without o tel', function (): void {
    $headers = [];
    TraceContextUtil::injectTraceContext($headers);
    expect($headers)->not->toHaveKey('traceparent');
});

test('does not inject tracestate without o tel', function (): void {
    $headers = [];
    TraceContextUtil::injectTraceContext($headers);
    expect($headers)->not->toHaveKey('tracestate');
});

test('preserves authorization header', function (): void {
    $headers = ['Authorization' => 'Bearer token123'];
    TraceContextUtil::injectTraceContext($headers);
    expect($headers['Authorization'])->toEqual('Bearer token123');
});

test('preserves content type header', function (): void {
    $headers = ['Content-Type' => 'application/json'];
    TraceContextUtil::injectTraceContext($headers);
    expect($headers['Content-Type'])->toEqual('application/json');
});

test('preserves x request id header', function (): void {
    $headers = ['X-Request-ID' => 'req-12345'];
    TraceContextUtil::injectTraceContext($headers);
    expect($headers['X-Request-ID'])->toEqual('req-12345');
});

test('preserves all existing headers', function (): void {
    $headers = [
        'Authorization' => 'Bearer token',
        'Content-Type' => 'application/json',
        'X-Request-ID' => 'abc-123',
    ];
    TraceContextUtil::injectTraceContext($headers);
    expect($headers)->toHaveCount(3);
    expect($headers['Authorization'])->toEqual('Bearer token');
    expect($headers['Content-Type'])->toEqual('application/json');
    expect($headers['X-Request-ID'])->toEqual('abc-123');
});

// .NET-specific scenario: PHP has no ambient tracer like .NET Activity.Current;
// injecting a real active span requires a fully configured OpenTelemetry SDK.
test('injects traceparent when a span is active', function (): void {
})->skip('no ambient tracer; active-span injection requires a configured OpenTelemetry SDK');

// .NET-specific scenario: setting tracestate on an active span requires a fully
// configured OpenTelemetry SDK, which is out of scope for this unit test.
test('includes tracestate when present on the active span', function (): void {
})->skip('no ambient tracer; tracestate-present requires a configured OpenTelemetry SDK');

// .NET-specific scenario: exercising an empty tracestate on an active span
// requires a fully configured OpenTelemetry SDK, which is out of scope here.
test('omits tracestate when empty on the active span', function (): void {
})->skip('no ambient tracer; empty-tracestate requires a configured OpenTelemetry SDK');

// .NET-specific scenario: verifying the recorded trace-flags byte requires a
// fully configured OpenTelemetry SDK with an active span.
test('formats trace flags correctly on the active span', function (): void {
})->skip('no ambient tracer; trace-flags formatting requires a configured OpenTelemetry SDK');
