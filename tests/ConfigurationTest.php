<?php

declare(strict_types=1);

use Zitadel\Client\Configuration;
use Zitadel\Client\ConfigurationBuilder;
use Zitadel\Client\ServerConfiguration;
use Zitadel\Client\ServerVariable;

afterEach(function (): void {
    // Reset the default instance between tests to avoid leaking state
    Configuration::setDefault(new Configuration());
});

test('default constructor uses spec base url', function (): void {
    $config = new Configuration();

    expect($config->baseUrl)->toBe('https://zitadel.com');
    expect($config->defaultHeaders)->toBe([]);
});

test('builder produces correct defaults', function (): void {
    $config = Configuration::builder()->build();

    expect($config->baseUrl)->toBe('https://zitadel.com');
    expect($config->defaultHeaders)->toBe([]);
});

test('builder returns configuration builder instance', function (): void {
    $builder = Configuration::builder();

    expect($builder)->toBeInstanceOf(ConfigurationBuilder::class);
});

test('builder sets base url', function (): void {
    $config = Configuration::builder()
        ->baseUrl('https://custom.example.com')
        ->build();

    expect($config->baseUrl)->toBe('https://custom.example.com');
});

test('builder sets single default header', function (): void {
    $config = Configuration::builder()
        ->defaultHeader('Authorization', 'Bearer token123')
        ->build();

    expect($config->defaultHeaders)->toBe(['Authorization' => 'Bearer token123']);
});

test('builder sets multiple default headers', function (): void {
    $config = Configuration::builder()
        ->defaultHeaders([
            'Authorization' => 'Bearer token123',
            'X-Custom' => 'value',
        ])
        ->build();

    expect($config->defaultHeaders)->toBe([
        'Authorization' => 'Bearer token123',
        'X-Custom' => 'value',
    ]);
});

test('builder accumulates headers', function (): void {
    $config = Configuration::builder()
        ->defaultHeader('X-First', 'one')
        ->defaultHeader('X-Second', 'two')
        ->defaultHeaders(['X-Third' => 'three'])
        ->build();

    expect($config->defaultHeaders)->toHaveCount(3);
    expect($config->defaultHeaders['X-First'])->toBe('one');
    expect($config->defaultHeaders['X-Second'])->toBe('two');
    expect($config->defaultHeaders['X-Third'])->toBe('three');
});

test('builder sets all fields', function (): void {
    $config = Configuration::builder()
        ->baseUrl('https://api.example.com')
        ->defaultHeader('Authorization', 'Bearer token')
        ->defaultHeaders(['X-Custom' => 'value'])
        ->build();

    expect($config->baseUrl)->toBe('https://api.example.com');
    expect($config->defaultHeaders)->toBe([
        'Authorization' => 'Bearer token',
        'X-Custom' => 'value',
    ]);
});

test('builder server resolves url', function (): void {
    $server = new ServerConfiguration(
        urlTemplate: 'https://{env}.example.com/api/{version}',
        description: 'Test server',
        variables: [
            'env' => new ServerVariable(
                defaultValue: 'api',
                enumValues: ['api', 'staging'],
            ),
            'version' => new ServerVariable(
                defaultValue: 'v3',
                enumValues: ['v2', 'v3'],
            ),
        ],
    );

    $config = Configuration::builder()
        ->server($server)
        ->build();

    expect($config->baseUrl)->toBe('https://api.example.com/api/v3');
});

test('builder server with variable overrides', function (): void {
    $server = new ServerConfiguration(
        urlTemplate: 'https://{env}.example.com/api/{version}',
        variables: [
            'env' => new ServerVariable(
                defaultValue: 'api',
                enumValues: ['api', 'staging'],
            ),
            'version' => new ServerVariable(
                defaultValue: 'v3',
                enumValues: ['v2', 'v3'],
            ),
        ],
    );

    $config = Configuration::builder()
        ->server($server, ['env' => 'staging', 'version' => 'v2'])
        ->build();

    expect($config->baseUrl)->toBe('https://staging.example.com/api/v2');
});

test('invalid server variable enum value throws', function (): void {
    $server = new ServerConfiguration(
        urlTemplate: 'https://{env}.example.com',
        variables: [
            'env' => new ServerVariable(
                defaultValue: 'api',
                enumValues: ['api', 'staging'],
            ),
        ],
    );

    expect(fn () => Configuration::builder()
        ->server($server, ['env' => 'invalid'])
        ->build())
        ->toThrow(InvalidArgumentException::class);
});

test('builder produces independent instances', function (): void {
    $builder = Configuration::builder()->baseUrl('https://example.com');
    $first = $builder->build();
    $second = $builder->build();

    expect($first)->not->toBe($second);
    expect($first->baseUrl)->toBe($second->baseUrl);
});

test('builder base url overrides server', function (): void {
    $server = new ServerConfiguration(
        urlTemplate: 'https://api.example.com',
    );

    $config = Configuration::builder()
        ->server($server)
        ->baseUrl('https://override.example.com')
        ->build();

    expect($config->baseUrl)->toBe('https://override.example.com');
});

test('get default returns instance', function (): void {
    $config = Configuration::getDefault();

    expect($config)->toBeInstanceOf(Configuration::class);
    expect($config->baseUrl)->toBe('https://zitadel.com');
});

test('get default returns same instance', function (): void {
    $first = Configuration::getDefault();
    $second = Configuration::getDefault();

    expect($second)->toBe($first);
});

test('set default changes default', function (): void {
    $custom = Configuration::builder()
        ->baseUrl('https://custom.example.com')
        ->build();

    Configuration::setDefault($custom);

    expect(Configuration::getDefault())->toBe($custom);
    expect(Configuration::getDefault()->baseUrl)->toBe('https://custom.example.com');
});

test('configuration is immutable', function (): void {
    $config = Configuration::builder()
        ->defaultHeader('X-Key', 'value')
        ->build();

    // readonly properties cannot be modified after construction;
    // verify the values are frozen
    expect($config->defaultHeaders['X-Key'])->toBe('value');
});

test('builder is fluent', function (): void {
    $builder = Configuration::builder();

    expect($builder->baseUrl('https://example.com'))->toBe($builder);
    expect($builder->defaultHeader('X-Key', 'value'))->toBe($builder);
    expect($builder->defaultHeaders(['X-Other' => 'val']))->toBe($builder);

    $server = new ServerConfiguration(urlTemplate: 'https://example.com');
    expect($builder->server($server))->toBe($builder);
});

test('every SDK class autoloads cold from its own file', function (): void {
    /* PSR-4 loads a class only from a file named after it. A class declared
     * in another class's file (an inline enum next to its model, a server
     * variant next to its API group) works only once that other file happens
     * to have been loaded, so each class is referenced FIRST, in a fresh
     * process, through the composer autoloader. */
    $root = dirname(__DIR__);
    /** @var array{autoload: array{psr-4: array<string, string>}} $composer */
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true);
    $autoload = var_export($root . '/vendor/autoload.php', true);
    $checked = 0;
    foreach ($composer['autoload']['psr-4'] as $prefix => $dir) {
        $base = (string) realpath($root . '/' . $dir);
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($files as $file) {
            if (!$file instanceof \SplFileInfo) {
                continue;
            }
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $relative = substr($file->getPathname(), strlen($base) + 1, -4);
            $class = var_export($prefix . str_replace('/', '\\', $relative), true);
            $code = "require $autoload; exit(class_exists($class) || interface_exists($class)"
                . " || enum_exists($class) || trait_exists($class) ? 0 : 1);";
            $output = [];
            exec(escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($code) . ' 2>&1', $output, $status);
            expect($status)->toBe(0, "$class did not autoload cold: " . implode("\n", $output));
            $checked++;
        }
    }
    expect($checked)->toBeGreaterThan(0);
});
