<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([__DIR__])
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
    ])
    ->withSkip([
        __DIR__ . '/vendor',
        // Mustache cannot produce PHP string interpolation ("$var") syntax
        \Rector\CodeQuality\Rector\Concat\JoinStringConcatRector::class,
        // Temp variables needed for PHPStan @var type assertions
        \Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector::class,
        // Constructor property promotion (PHP 8.0+) collapses explicit
        // per-property docblocks that the generator's templates rely on
        // for type hints and rector-resistant doc comments.
        \Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector::class,
        // Readonly-class promotion requires all properties immutable;
        // OAuth2TokenManager mutates accessToken/expiresAt internally.
        \Rector\Php82\Rector\Class_\ReadOnlyClassRector::class,
        // Same for per-property: OAuth2TokenManager state mutates;
        // we avoid `readonly` to keep mustache emission uniform.
        \Rector\Php81\Rector\Property\ReadOnlyPropertyRector::class,
        // Both write fully-qualified class names into the code (an
        // instanceof in place of a null check, a return type on an arrow
        // function), which pushes generated lines past PSR-12's 120 columns.
        \Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector::class,
        \Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector::class,
        // Reads the redirect loop's `($response->headers['location'] ??
        // [null])[0]` as always null, rewrites the guard under it into an
        // unconditional `break`, and RemoveUnreachableStatementRector then
        // deletes the rest of the loop. The inference is wrong and the
        // deletion is silent, so the rule stays off.
        \Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector::class,
    ]);
