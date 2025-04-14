<?php

namespace Zitadel\Client\Spec;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;

class Setup implements TestListener
{
  use TestListenerDefaultImplementation;

  public function startTestSuite(TestSuite $suite): void
  {
    $dotenv = Dotenv::createImmutable(getcwd());
    /** @noinspection PhpStatementHasEmptyBodyInspection */
    if (empty($dotenv->safeLoad())) {
      //
    }
  }
}
