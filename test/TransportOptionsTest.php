<?php

namespace Zitadel\Client\Test;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\TransportOptions;

class TransportOptionsTest extends TestCase
{
    public function testDefaultsReturnsEmpty(): void
    {
        $this->assertSame([], TransportOptions::defaults()->toGuzzleOptions());
    }

    public function testInsecureSetsVerifyFalse(): void
    {
        $opts = new TransportOptions(insecure: true);
        $result = $opts->toGuzzleOptions();
        $this->assertFalse($result['verify']);
    }

    public function testCaCertPathSetsVerify(): void
    {
        $caCertPath = __DIR__ . '/fixtures/ca.pem';
        $opts = new TransportOptions(caCertPath: $caCertPath);
        $result = $opts->toGuzzleOptions();
        $this->assertSame($caCertPath, $result['verify']);
    }

    public function testProxyUrlSetsProxy(): void
    {
        $opts = new TransportOptions(proxyUrl: 'http://proxy:3128');
        $result = $opts->toGuzzleOptions();
        $this->assertSame('http://proxy:3128', $result['proxy']);
    }

    public function testDefaultHeadersSetsHeaders(): void
    {
        $opts = new TransportOptions(defaultHeaders: ['X-Custom' => 'value']);
        $result = $opts->toGuzzleOptions();
        $this->assertSame(['X-Custom' => 'value'], $result['headers']);
    }

    public function testInsecureTakesPrecedenceOverCaCert(): void
    {
        $opts = new TransportOptions(caCertPath: '/path/to/ca.pem', insecure: true);
        $result = $opts->toGuzzleOptions();
        $this->assertFalse($result['verify']);
    }

    public function testDefaultsFactory(): void
    {
        $opts = TransportOptions::defaults();
        $this->assertSame([], $opts->defaultHeaders);
        $this->assertNull($opts->caCertPath);
        $this->assertFalse($opts->insecure);
        $this->assertNull($opts->proxyUrl);
    }
}
