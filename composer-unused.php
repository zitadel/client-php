<?php

declare(strict_types=1);

use ComposerUnused\ComposerUnused\Configuration\Configuration;
use ComposerUnused\ComposerUnused\Configuration\NamedFilter;

return static function (Configuration $config): Configuration {
    // ext-curl backs symfony/http-client's CurlHttpClient, which is the
    // transport the SDK uses at runtime. composer-unused cannot see the
    // extension being used through the HttpClient factory, so mark it used.
    $config->addNamedFilter(NamedFilter::fromString('ext-curl'));

    // phpdocumentor/reflection-docblock is consumed by
    // symfony/property-info's PhpDocExtractor, which the serializer relies
    // on to read PHPDoc type hints while hydrating models. The usage is
    // resolved through Symfony's service wiring, so it is invisible to the
    // static scan.
    $config->addNamedFilter(NamedFilter::fromString('phpdocumentor/reflection-docblock'));

    return $config;
};
