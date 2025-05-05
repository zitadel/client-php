<?php

/** @noinspection PhpUnused */

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/lib',
        __DIR__ . '/spec',
        __DIR__ . '/test',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/lib/Api',
        __DIR__ . '/lib/Model',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
    ]);
};
