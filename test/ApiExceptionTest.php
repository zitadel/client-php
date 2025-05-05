<?php

namespace Zitadel\Client\Test;

use PHPUnit\Framework\TestCase;
use Zitadel\Client\ApiException;

class ApiExceptionTest extends TestCase
{
    public function testApiException(): void
    {
        $e = new ApiException('Error 418', 418, ['H' => ['v']], 'body');
        $this->assertSame('Error 418', $e->getMessage());
        $this->assertSame(418, $e->getCode());
        $this->assertSame(['H' => ['v']], $e->getResponseHeaders());
        $this->assertSame('body', $e->getResponseBody());
    }
}
