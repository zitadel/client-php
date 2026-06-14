# zitadel/client SDK

Auto-generated PHP SDK client for the Zitadel SDK API.

## Requirements

- PHP 8.5 or newer
- Composer 2.x
- PHP extensions: `ext-curl`, `ext-json`, `ext-mbstring`
- Optional: PECL `ext-ds` (auto-replaces the `php-ds/php-ds` userland
  `\Ds\Vector` / `\Ds\Set` / `\Ds\Map` classes with their native C
  implementations for ~5–10x throughput on container-heavy payloads)

## Install

```bash
composer install
```

## Test

```bash
vendor/bin/pest
```

## Tooling

The generated project ships with a complete formatter, linter, static
analyser, and Rector configuration. Run any of them via Composer's
`vendor/bin`:

```bash
# Formatter — PHP-CS-Fixer (PHP84Migration + PHP82Migration:risky rulesets)
vendor/bin/php-cs-fixer fix

# Linter — PHP_CodeSniffer (PSR-12 + SlevomatCodingStandard)
vendor/bin/phpcs

# Static analyser — PHPStan at level 9 (max)
vendor/bin/phpstan analyse

# Automated upgrades — Rector (UP_TO_PHP_84 + DEAD_CODE + CODE_QUALITY + TYPE_DECLARATION)
vendor/bin/rector
```

## Package

- Name: `Zitadel\Client`
- Version: `1.0.0`

## Caveats

### Integer and decimal precision

Symfony's serializer (used internally for JSON deserialization)
downgrades `format: int64` values through PHP `float` before assigning
them to model fields. On 64-bit PHP this means values larger than
2^53 silently lose precision — the same IEEE-754 ceiling that bites
JavaScript and Dart. Snowflake / Twitter / Discord-style IDs above
9 007 199 254 740 991 are rounded.

`format: decimal` / `format: number` fields are likewise stored as
PHP `float`, so monetary values lose their exact decimal
representation. `0.1 + 0.2` in PHP is `0.30000000000000004` — do not
do arithmetic on prices, balances, or other money-typed fields. Pass
them through as strings via `BCMath` if you need exact arithmetic.

Fixing this end-to-end would require switching the wire layer to
emit JSON numbers from internal string/GMP representations, which
breaks `format: int64` schema validation against strict mock servers
(verified with Chasm). Documented as a known limitation.

## Not supported

### Webhooks and callbacks

This SDK is **client → server** only. Spec entries describing
server-initiated calls — OAS 3.1 top-level `webhooks` and OAS 3.0
per-operation `callbacks` — are intentionally skipped during code
generation. If you need to receive webhook deliveries, write the
handler yourself and use this SDK only to deserialize the incoming
payload (e.g. by reusing the relevant request-body model).

### Conditional-required validation (`dependentRequired` / `dependentSchemas`)

JSON Schema 2019-09 keywords for "if field X is present, field Y is
also required" are **not enforced** by this SDK. No mainstream
OpenAPI client codegen implements them. The server is the authoritative
validator; if you want client-side checking, plug in a JSON Schema
validator library for your language.

### Numeric / string constraint validation

OpenAPI keywords like `minLength`, `maxLength`, `minimum`, `maximum`,
`pattern`, `minItems`, `maxItems`, `uniqueItems`, `multipleOf` are
**not enforced** by this SDK. The server is the authoritative
validator; client-side enforcement is a DX nicety, not a correctness
requirement. If you want fast-fail validation before the network
round trip, plug in a JSON Schema validator library for your language.

### SOCKS proxies

`TransportOptions.proxy()` accepts only `http://` and `https://` URLs.
Passing a `socks://`, `socks4://`, or `socks5://` scheme throws (or
panics) at construction time with a clear error. SOCKS support would
require enabling extra dependencies / feature flags on the underlying
HTTP library in every one of the 12 SDKs we generate, with non-trivial
API divergence; we explicitly chose not to. If you need SOCKS, route
through a local HTTP-CONNECT bridge or configure it at the OS level.

### Per-call cancellation

No generated operation method accepts a per-call cancellation handle.
In-flight requests can only be terminated by waiting for the configured
`TransportOptions` request timeout to fire — there is no way to abort
mid-flight from the caller side. If you need fine-grained per-call
cancellation, wrap the SDK call in your language's standard concurrency
primitives (a `Future` you cancel externally, a `Task` you orphan, an
`asyncio` task you cancel, etc.) and rely on the timeout to break the
underlying socket.

### `LICENSE` file is not auto-emitted

The package manifest declares MIT, but no `LICENSE` / `LICENSE.md` file
is generated alongside the sources. Drop the appropriate license text
into the generated tree as part of your release pipeline before
publishing to a registry — most registries warn or block on a missing
file, and the GitHub license auto-detect cannot pick up a manifest-only
declaration.

### `serializeValue` non-styled path-array branch is unsafe (dead code)

`ObjectSerializer::serializeValue` has a legacy non-styled branch that
joins path-array items with `,` **without** percent-encoding each item
first. The sibling fix landed for Dart (`_serializeArray`), but this
PHP branch is left as-is because the PHP `invokeApi` routing layer
always goes through the styled path (`serializeStyled`) for array path
parameters — the unsafe branch is currently dead code in the
generation pipeline. If a future refactor wires array path params
through the legacy path, this will become a real wire bug. Documented
here as a latent hazard rather than fixed pre-emptively.
