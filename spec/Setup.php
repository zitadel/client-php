<?php /** @noinspection PhpMissingFieldTypeInspection */

/** @noinspection PhpDeprecationInspection */

namespace Zitadel\Client\Spec;

use DOMDocument;
use DOMElement;
use DOMException;
use Dotenv\Dotenv;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Util\Filter;
use PHPUnit\Util\Printer;
use PHPUnit\Util\Xml;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Throwable;

/** @noinspection PhpUnused */

class Setup extends Printer implements TestListener
{
    /**
     * @var DOMDocument
     */
    private $document;

    /**
     * @var DOMElement
     */
    private $root;

    /**
     * @var DOMElement[]
     */
    private $testSuites = [];

    /**
     * @var int[]
     */
    private $testSuiteTests = [0];

    /**
     * @var int[]
     */
    private $testSuiteAssertions = [0];

    /**
     * @var int[]
     */
    private $testSuiteErrors = [0];

    /**
     * @var int[]
     */
    private $testSuiteWarnings = [0];

    /**
     * @var int[]
     */
    private $testSuiteFailures = [0];

    /**
     * @var int[]
     */
    private $testSuiteSkipped = [0];

    /**
     * @var int[]
     */
    private $testSuiteTimes = [0];

    /**
     * @var int
     */
    private $testSuiteLevel = 0;

    /**
     * @var DOMElement|null
     */
    private $currentTestCase;

    /**
     * @param null|mixed $out
     * @throws DOMException
     */
    public function __construct($out = null, private bool $reportRiskyTests = false)
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;

        $this->root = $this->document->createElement('testsuites');
        $this->document->appendChild($this->root);

        if ($out === null) {
            $directory = 'build/reports';
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $out = fopen($directory . '/junit.xml', 'w');

            if ($out === false) {
                throw new RuntimeException('Failed to open build/reports/junit.xml for writing');
            }
        }
        parent::__construct($out);
    }

    /**
     * Flush buffer and close output.
     */
    public function flush(): void
    {
        $this->write($this->getXML());

        parent::flush();
    }

    /**
     * An error occurred.
     * @throws DOMException
     */
    public function addError(Test $test, Throwable $t, float $time): void
    {
        $this->doAddFault($test, $t, 'error');
        $this->testSuiteErrors[$this->testSuiteLevel]++;
    }

    /**
     * A warning occurred.
     * @throws DOMException
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->doAddFault($test, $e, 'warning');
        $this->testSuiteWarnings[$this->testSuiteLevel]++;
    }

    /**
     * A failure occurred.
     * @throws DOMException
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->doAddFault($test, $e, 'failure');
        $this->testSuiteFailures[$this->testSuiteLevel]++;
    }

    /**
     * Incomplete test.
     * @throws DOMException
     */
    public function addIncompleteTest(Test $test, Throwable $t, float $time): void
    {
        $this->doAddSkipped();
    }

    /**
     * Risky test.
     * @throws DOMException
     */
    public function addRiskyTest(Test $test, Throwable $t, float $time): void
    {
        if (!$this->reportRiskyTests) {
            return;
        }

        $this->doAddFault($test, $t, 'error');
        $this->testSuiteErrors[$this->testSuiteLevel]++;
    }

    /**
     * Skipped test.
     * @throws DOMException
     */
    public function addSkippedTest(Test $test, Throwable $t, float $time): void
    {
        $this->doAddSkipped();
    }

    /**
     * A testsuite started.
     * @throws DOMException
     */
    public function startTestSuite(TestSuite $suite): void
    {
        $dotenv = Dotenv::createImmutable(getcwd());
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        if (empty($dotenv->safeLoad())) {
            //
        }

        if (!class_exists($suite->getName(), false)) {
            return; // skip suites for non-existent classes
        }

        $testSuite = $this->document->createElement('testsuite');
        $testSuite->setAttribute('name', $suite->getName());
        $testSuite->setAttribute('timestamp', date('c'));
        $testSuite->setAttribute('hostname', gethostname() ?: 'localhost');

        try {
            $class = new ReflectionClass($suite->getName());
            $testSuite->setAttribute('file', self::toRelativePath($class->getFileName()));
        } catch (ReflectionException) {
            return; // skip if reflection fails (shouldn't happen after class_exists)
        }

        if ($this->testSuiteLevel > 0) {
            $this->testSuites[$this->testSuiteLevel]->appendChild($testSuite);
        } else {
            $this->root->appendChild($testSuite);
        }

        $this->testSuiteLevel++;
        $this->testSuites[$this->testSuiteLevel] = $testSuite;
        $this->testSuiteTests[$this->testSuiteLevel] = 0;
        $this->testSuiteAssertions[$this->testSuiteLevel] = 0;
        $this->testSuiteErrors[$this->testSuiteLevel] = 0;
        $this->testSuiteWarnings[$this->testSuiteLevel] = 0;
        $this->testSuiteFailures[$this->testSuiteLevel] = 0;
        $this->testSuiteSkipped[$this->testSuiteLevel] = 0;
        $this->testSuiteTimes[$this->testSuiteLevel] = 0;
    }

    /**
     * A testsuite ended.
     */
    public function endTestSuite(TestSuite $suite): void
    {
        if (!isset($this->testSuites[$this->testSuiteLevel])) {
            $this->testSuiteLevel = max(0, $this->testSuiteLevel - 1);
            return;
        }

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'tests',
            (string)$this->testSuiteTests[$this->testSuiteLevel],
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'assertions',
            (string)$this->testSuiteAssertions[$this->testSuiteLevel],
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'errors',
            (string)$this->testSuiteErrors[$this->testSuiteLevel],
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'warnings',
            (string)$this->testSuiteWarnings[$this->testSuiteLevel],
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'failures',
            (string)$this->testSuiteFailures[$this->testSuiteLevel],
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'skipped',
            (string)$this->testSuiteSkipped[$this->testSuiteLevel],
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'time',
            sprintf('%F', $this->testSuiteTimes[$this->testSuiteLevel]),
        );

        if ($this->testSuiteLevel > 1) {
            $this->testSuiteTests[$this->testSuiteLevel - 1] += $this->testSuiteTests[$this->testSuiteLevel];
            $this->testSuiteAssertions[$this->testSuiteLevel - 1] += $this->testSuiteAssertions[$this->testSuiteLevel];
            $this->testSuiteErrors[$this->testSuiteLevel - 1] += $this->testSuiteErrors[$this->testSuiteLevel];
            $this->testSuiteWarnings[$this->testSuiteLevel - 1] += $this->testSuiteWarnings[$this->testSuiteLevel];
            $this->testSuiteFailures[$this->testSuiteLevel - 1] += $this->testSuiteFailures[$this->testSuiteLevel];
            $this->testSuiteSkipped[$this->testSuiteLevel - 1] += $this->testSuiteSkipped[$this->testSuiteLevel];
            $this->testSuiteTimes[$this->testSuiteLevel - 1] += $this->testSuiteTimes[$this->testSuiteLevel];
        }

        $this->testSuiteLevel--;
    }

    /**
     * A test started.
     * @throws DOMException
     */
    public function startTest(Test $test): void
    {
        if (!$test instanceof TestCase) {
            return;
        }
        $usesDataprovider = $test->usesDataProvider();

        $testCase = $this->document->createElement('testcase');
        $testCase->setAttribute('name', $test->getName());

        $class = new ReflectionClass($test);
        // @codeCoverageIgnoreEnd

        $methodName = $test->getName(!$usesDataprovider);

        if ($class->hasMethod($methodName)) {
            $method = $class->getMethod($methodName);
            // @codeCoverageIgnoreEnd

            $testCase->setAttribute('class', $class->getName());
            $testCase->setAttribute('classname', str_replace('\\', '.', $class->getName()));
            $testCase->setAttribute('file', Setup::toRelativePath($class->getFileName()));
            $testCase->setAttribute('line', (string)$method->getStartLine());
        }

        $this->currentTestCase = $testCase;
    }

    /**
     * A test ended.
     * @throws DOMException
     */
    public function endTest(Test $test, float $time): void
    {
        $numAssertions = 0;

        if (method_exists($test, 'getNumAssertions')) {
            $numAssertions = $test->getNumAssertions();
        }

        $this->testSuiteAssertions[$this->testSuiteLevel] += $numAssertions;

        $this->currentTestCase->setAttribute(
            'assertions',
            (string)$numAssertions,
        );

        $this->currentTestCase->setAttribute(
            'time',
            sprintf('%F', $time),
        );

        $this->testSuites[$this->testSuiteLevel]->appendChild(
            $this->currentTestCase,
        );

        $this->testSuiteTests[$this->testSuiteLevel]++;
        $this->testSuiteTimes[$this->testSuiteLevel] += $time;

        $testOutput = '';

        if (method_exists($test, 'hasOutput') && method_exists($test, 'getActualOutput')) {
            $testOutput = $test->hasOutput() ? $test->getActualOutput() : '';
        }

        if (!empty($testOutput)) {
            $systemOut = $this->document->createElement(
                'system-out',
                Xml::prepareString($testOutput),
            );

            $this->currentTestCase->appendChild($systemOut);
        }

        $this->currentTestCase = null;
    }

    /**
     * Returns the XML as a string.
     */
    public function getXML(): string
    {
        return $this->document->saveXML();
    }

    /**
     * @throws DOMException
     */
    private function doAddFault(Test $test, Throwable $t, string $type): void
    {
        if ($this->currentTestCase === null) {
            return;
        }

        if ($test instanceof SelfDescribing) {
            $buffer = $test->toString() . "\n";
        } else {
            $buffer = '';
        }

        $buffer .= trim(
            TestFailure::exceptionToString($t) . "\n" .
      Filter::getFilteredStacktrace($t),
        );

        $fault = $this->document->createElement(
            $type,
            Xml::prepareString($buffer),
        );

        if ($t instanceof ExceptionWrapper) {
            $fault->setAttribute('type', $t->getClassName());
        } else {
            $fault->setAttribute('type', $t::class);
        }

        $this->currentTestCase->appendChild($fault);
    }

    /**
     * @throws DOMException
     */
    private function doAddSkipped(): void
    {
        if ($this->currentTestCase === null) {
            return;
        }

        $skipped = $this->document->createElement('skipped');

        $this->currentTestCase->appendChild($skipped);

        $this->testSuiteSkipped[$this->testSuiteLevel]++;
    }

    private static function toRelativePath(string $absolutePath): string
    {
        $cwd = getcwd();

        if (str_starts_with($absolutePath, $cwd)) {
            $relative = substr($absolutePath, strlen($cwd));
            return ltrim($relative, DIRECTORY_SEPARATOR);
        }

        return $absolutePath;
    }
}
