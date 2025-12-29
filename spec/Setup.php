<?php

declare(strict_types=1);

namespace Zitadel\Client\Spec;

use Dotenv\Dotenv;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * PHPUnit extension that:
 * 1. Loads environment variables from .env files
 * 2. Enhances JUnit XML output with timestamp, hostname, and warnings attributes
 */
final class Setup implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $dotenv = Dotenv::createImmutable(getcwd());
        $dotenv->safeLoad();

        new JUnitReporter('build/reports/junit-raw.xml', 'build/reports/junit.xml');
    }
}
