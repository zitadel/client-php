<?php

declare(strict_types=1);

namespace Zitadel\Client\Spec;

use DOMDocument;

/**
 * Enhances PHPUnit's built-in JUnit XML output by adding missing attributes:
 * - timestamp (on testsuite)
 * - hostname (on testsuite)
 * - warnings (on testsuite)
 *
 * Also converts absolute paths to relative paths for portability.
 */
final readonly class JUnitReporter
{
    public function __construct(
        private string $inputPath,
        private string $outputPath,
    ) {
        register_shutdown_function([$this, 'enhance']);
    }

    public function enhance(): void
    {
        if (!file_exists($this->inputPath)) {
            return;
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        if (!$doc->load($this->inputPath)) {
            return;
        }

        $hostname = gethostname() ?: 'localhost';
        $timestamp = date('c');

        foreach ($doc->getElementsByTagName('testsuite') as $suite) {
            $attrs = [];
            foreach ($suite->attributes as $attr) {
                $attrs[$attr->name] = $attr->value;
            }

            while ($suite->attributes->length > 0) {
                $suite->removeAttribute($suite->attributes->item(0)->name);
            }

            $suite->setAttribute('name', $attrs['name'] ?? '');
            $suite->setAttribute('timestamp', $timestamp);
            $suite->setAttribute('hostname', $hostname);
            if (isset($attrs['file'])) {
                $suite->setAttribute('file', $this->relativePath($attrs['file']));
            }
            $suite->setAttribute('tests', $attrs['tests'] ?? '0');
            $suite->setAttribute('assertions', $attrs['assertions'] ?? '0');
            $suite->setAttribute('errors', $attrs['errors'] ?? '0');
            $suite->setAttribute('warnings', '0');
            $suite->setAttribute('failures', $attrs['failures'] ?? '0');
            $suite->setAttribute('skipped', $attrs['skipped'] ?? '0');
            $suite->setAttribute('time', $attrs['time'] ?? '0');
        }

        foreach ($doc->getElementsByTagName('testcase') as $testcase) {
            if ($testcase->hasAttribute('file')) {
                $testcase->setAttribute('file', $this->relativePath($testcase->getAttribute('file')));
            }
        }

        $dir = dirname($this->outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $doc->save($this->outputPath);
    }

    private function relativePath(string $path): string
    {
        $cwd = getcwd();
        return $cwd && str_starts_with($path, $cwd) ? ltrim(substr($path, strlen($cwd)), DIRECTORY_SEPARATOR) : $path;
    }
}
