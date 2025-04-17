<?php

/** @noinspection PhpDeprecationInspection */

namespace Zitadel\Client\Spec;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;

/** @noinspection PhpUnused */

class Setup implements TestListener
{
    use TestListenerDefaultImplementation;

    /** @noinspection PhpUnused */
    public function startTestSuite(TestSuite $suite): void
    {
        $dotenv = Dotenv::createImmutable(getcwd());
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        if (empty($dotenv->safeLoad())) {
            //
        }
    }
}
