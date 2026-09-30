<?php

declare(strict_types=1);

use Zitadel\Client\ServerVariable;

test('default value is exposed', function (): void {
    $variable = new ServerVariable(defaultValue: 'v3');

    expect($variable->defaultValue)->toBe('v3');
});

test('description defaults to null', function (): void {
    $variable = new ServerVariable(defaultValue: 'v3');

    expect($variable->description)->toBeNull();
});

test('enum values default to empty array', function (): void {
    $variable = new ServerVariable(defaultValue: 'v3');

    expect($variable->enumValues)->toBe([]);
});

test('all fields are exposed', function (): void {
    $variable = new ServerVariable(
        defaultValue: 'v3',
        description: 'API version',
        enumValues: ['v2', 'v3'],
    );

    expect($variable->defaultValue)->toBe('v3');
    expect($variable->description)->toBe('API version');
    expect($variable->enumValues)->toBe(['v2', 'v3']);
});
