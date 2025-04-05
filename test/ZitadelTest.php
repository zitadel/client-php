<?php

namespace Zitadel\Client\Test;

use HaydenPierce\ClassFinder\ClassFinder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Zitadel\Client\Auth\NoAuthAuthenticator;
use Zitadel\Client\Zitadel;

/**
 * This test verifies that all API service classes in the "Zitadel\Client\Api" namespace,
 * as discovered by the haydenpierce/class-finder library, are registered as typed properties
 * in the Zitadel class. This ensures that every API service is properly registered in the SDK.
 */
class ZitadelTest extends TestCase
{
  /**
   * Verifies that the set of expected API service classes matches the set of actual service properties in Zitadel.
   * @throws \Exception when it is unable to look up classes in the namespace
   */
  public function testServicesDynamic(): void
  {
    $expected = ClassFinder::getClassesInNamespace('Zitadel\Client\Api');
    $expected = array_filter($expected, function (string $class): bool {
      return substr($class, -10) === 'ServiceApi';
    });
    sort($expected);

    $zitadel = new Zitadel(new NoAuthAuthenticator());
    $reflection = new ReflectionClass($zitadel);
    $properties = $reflection->getProperties();
    $actual = [];
    foreach ($properties as $prop) {
      $type = $prop->getType();
      if ($type !== null && strpos($type->getName(), 'Zitadel\Client\Api\\') === 0) {
        $actual[] = $type->getName();
      }
    }
    sort($actual);

    $this->assertEquals(array_values($expected), array_values($actual));
  }
}
