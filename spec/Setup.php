<?php

namespace Zitadel\Client\Spec;

use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;
use Dotenv\Dotenv;

class Setup implements TestListener
{
	use TestListenerDefaultImplementation;

	public function startTestSuite(TestSuite $suite): void
	{
		$dotenv = Dotenv::createImmutable(getcwd());
		$dotenv->load();
	}
}
