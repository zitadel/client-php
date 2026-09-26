<?php

declare(strict_types=1);

use Zitadel\Client\TraceContextUtil;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

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

/**
 * Registers an OpenTelemetry SDK with the W3C trace-context propagator and an
 * in-memory exporter as the global instance for the duration of $body.
 *
 * @param callable(InMemoryExporter, TracerProvider): void $body
 */
function withTestTracing(callable $body): void
{
    $exporter = new InMemoryExporter();
    $tracerProvider = new TracerProvider(new SimpleSpanProcessor($exporter));
    $scope = Configurator::create()
        ->withTracerProvider($tracerProvider)
        ->withPropagator(TraceContextPropagator::getInstance())
        ->activate();
    try {
        $body($exporter, $tracerProvider);
    } finally {
        $scope->detach();
        $tracerProvider->shutdown();
    }
}

/**
 * Starts a span under $parent, injects the trace context while it is
 * current, ends it, and returns the injected headers with the span's ids.
 *
 * @return array<string, string>
 */
function injectTraceUnder(TracerProvider $tracerProvider, ContextInterface $parent): array
{
    $span = $tracerProvider->getTracer('trace-context-test')
        ->spanBuilder('request')
        ->setParent($parent)
        ->startSpan();
    $headers = [];
    /* Activate on the CURRENT context, not on $parent: $parent is derived
     * from the root context and carries none of the tracer provider and
     * propagator that withTestTracing() put on the current one, so
     * Globals::propagator() would fall back to the no-op propagator and
     * inject nothing. The span already carries $parent's trace id. */
    $spanScope = $span->activate();
    try {
        TraceContextUtil::injectTraceContext($headers);
    } finally {
        $spanScope->detach();
        $span->end();
    }
    $headers['x-test-trace-id'] = $span->getContext()->getTraceId();
    $headers['x-test-span-id'] = $span->getContext()->getSpanId();
    $headers['x-test-flags'] = $span->getContext()->isSampled() ? '01' : '00';

    return $headers;
}

function remoteTraceParent(int $flags, ?TraceState $state): ContextInterface
{
    return Span::wrap(SpanContext::createFromRemoteParent(
        '0af7651916cd43dd8448eb211c80319c',
        'b7ad6b7169203331',
        $flags,
        $state
    ))->storeInContext(Context::getRoot());
}

test('injects traceparent when a span is active', function (): void {
    withTestTracing(function (InMemoryExporter $exporter, TracerProvider $tracerProvider): void {
        $headers = injectTraceUnder($tracerProvider, Context::getRoot());
        expect($headers['traceparent'] ?? null)->toBe(
            '00-' . $headers['x-test-trace-id'] . '-' . $headers['x-test-span-id'] . '-' . $headers['x-test-flags']
        );
        expect($exporter->getSpans())->toHaveCount(1);
    });
});

test('includes tracestate when present on the active span', function (): void {
    withTestTracing(function (InMemoryExporter $exporter, TracerProvider $tracerProvider): void {
        $headers = injectTraceUnder(
            $tracerProvider,
            remoteTraceParent(TraceFlags::SAMPLED, new TraceState('vendor=value'))
        );
        expect($headers['traceparent'] ?? '')->toStartWith('00-0af7651916cd43dd8448eb211c80319c-');
        expect($headers['tracestate'] ?? null)->toBe('vendor=value');
    });
});

test('omits tracestate when empty on the active span', function (): void {
    withTestTracing(function (InMemoryExporter $exporter, TracerProvider $tracerProvider): void {
        $headers = injectTraceUnder($tracerProvider, remoteTraceParent(TraceFlags::SAMPLED, null));
        expect($headers)->toHaveKey('traceparent');
        expect($headers)->not->toHaveKey('tracestate');
    });
});

test('formats trace flags correctly on the active span', function (): void {
    withTestTracing(function (InMemoryExporter $exporter, TracerProvider $tracerProvider): void {
        $sampled = injectTraceUnder($tracerProvider, remoteTraceParent(TraceFlags::SAMPLED, null));
        $unsampled = injectTraceUnder($tracerProvider, remoteTraceParent(TraceFlags::DEFAULT, null));
        expect($sampled['traceparent'] ?? '')->toEndWith('-01');
        expect($unsampled['traceparent'] ?? '')->toEndWith('-00');
    });
});
