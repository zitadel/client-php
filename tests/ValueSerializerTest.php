<?php

declare(strict_types=1);

// phpcs:ignoreFile

use Zitadel\Client\ValueSerializer;

// -- path location --

test('path null returns empty string', function (): void {
    expect(ValueSerializer::serialize(null, 'path', 'string'))->toBe('');
});

test('path string returns url encoded value', function (): void {
    expect(ValueSerializer::serialize('hello', 'path', 'string'))->toBe('hello');
});

test('path string with spaces is url encoded', function (): void {
    expect(ValueSerializer::serialize('hello world', 'path', 'string'))->toBe('hello%20world');
});

test('path string with slash is url encoded', function (): void {
    expect(ValueSerializer::serialize('a/b', 'path', 'string'))->toBe('a%2Fb');
});

test('path integer returns string', function (): void {
    expect(ValueSerializer::serialize(42, 'path', 'integer'))->toBe('42');
});

test('path boolean true returns true', function (): void {
    expect(ValueSerializer::serialize(true, 'path', 'boolean'))->toBe('true');
});

test('path boolean false returns false', function (): void {
    expect(ValueSerializer::serialize(false, 'path', 'boolean'))->toBe('false');
});

test('path date only emits yyyy mm dd', function (): void {
    // Per W3/N3: format: date in path emits YYYY-MM-DD (no time).
    $dt = new \DateTime('2024-01-15T10:30:45+00:00');
    expect(ValueSerializer::serialize($dt, 'path', '\\DateTime|date'))->toBe('2024-01-15');
});

test('path date only styled simple emits yyyy mm dd', function (): void {
    $dt = new \DateTime('2024-01-15T10:30:45+00:00');
    expect(ValueSerializer::serializeStyled('d', $dt, 'path', '\\DateTime|date', null, 'simple', false))->toBe('2024-01-15');
});

test('path date time without date marker keeps full iso', function (): void {
    $dt = new \DateTime('2024-01-15T10:30:45+00:00');
    $result = ValueSerializer::serialize($dt, 'path', '\\DateTime');
    expect($result)->toBeString();
    expect($result)->toContain('2024-01-15');
    expect($result)->toContain('10');
});

// -- query location --

test('query null returns null', function (): void {
    expect(ValueSerializer::serialize(null, 'query', 'string'))->toBeNull();
});

test('query string returns as is', function (): void {
    expect(ValueSerializer::serialize('hello', 'query', 'string'))->toBe('hello');
});

test('query integer returns string', function (): void {
    expect(ValueSerializer::serialize(42, 'query', 'integer'))->toBe('42');
});

test('query boolean true returns true', function (): void {
    expect(ValueSerializer::serialize(true, 'query', 'boolean'))->toBe('true');
});

test('query boolean false returns false', function (): void {
    expect(ValueSerializer::serialize(false, 'query', 'boolean'))->toBe('false');
});

test('query array joins with comma by default', function (): void {
    expect(ValueSerializer::serialize(['a', 'b', 'c'], 'query', 'array'))->toBe('a,b,c');
});

test('query array joins with comma for csv', function (): void {
    expect(ValueSerializer::serialize(['a', 'b', 'c'], 'query', 'array', 'csv'))->toBe('a,b,c');
});

test('query array joins with space for ssv', function (): void {
    expect(ValueSerializer::serialize(['a', 'b', 'c'], 'query', 'array', 'ssv'))->toBe('a b c');
});

test('query array joins with tab for tsv', function (): void {
    expect(ValueSerializer::serialize(['a', 'b', 'c'], 'query', 'array', 'tsv'))->toBe("a\tb\tc");
});

test('query array joins with pipe for pipes', function (): void {
    expect(ValueSerializer::serialize(['a', 'b', 'c'], 'query', 'array', 'pipes'))->toBe('a|b|c');
});

test('query array returns list for multi', function (): void {
    expect(ValueSerializer::serialize(['a', 'b', 'c'], 'query', 'array', 'multi'))->toBe(['a', 'b', 'c']);
});

test('query empty array returns empty string for csv', function (): void {
    expect(ValueSerializer::serialize([], 'query', 'array'))->toBe('');
});

test('query empty array returns empty list for multi', function (): void {
    expect(ValueSerializer::serialize([], 'query', 'array', 'multi'))->toBe([]);
});

test('query single element array returns single value', function (): void {
    expect(ValueSerializer::serialize(['a'], 'query', 'array'))->toBe('a');
});

test('query array csv keeps slot for null element', function (): void {
    // Per W1/N1: a null element in a csv array becomes an empty slot, not skipped.
    expect(ValueSerializer::serialize([1, null, 3], 'query', 'array'))->toBe('1,,3');
});

test('query array csv explicit keeps slot for null element', function (): void {
    expect(ValueSerializer::serialize([1, null, 3], 'query', 'array', 'csv'))->toBe('1,,3');
});

test('query array multi keeps empty string for null element', function (): void {
    expect(ValueSerializer::serialize([1, null, 3], 'query', 'array', 'multi'))->toBe(['1', '', '3']);
});

test('query array of integers stringifies elements', function (): void {
    expect(ValueSerializer::serialize([1, 2, 3], 'query', 'array'))->toBe('1,2,3');
});

test('query array of booleans stringifies elements', function (): void {
    expect(ValueSerializer::serialize([true, false], 'query', 'array'))->toBe('true,false');
});

// -- header location --

test('header null returns empty string', function (): void {
    expect(ValueSerializer::serialize(null, 'header', 'string'))->toBe('');
});

test('header string returns as is', function (): void {
    expect(ValueSerializer::serialize('hello', 'header', 'string'))->toBe('hello');
});

test('header integer returns string', function (): void {
    expect(ValueSerializer::serialize(42, 'header', 'integer'))->toBe('42');
});

test('header boolean true returns true', function (): void {
    expect(ValueSerializer::serialize(true, 'header', 'boolean'))->toBe('true');
});

test('header array joins with comma', function (): void {
    expect(ValueSerializer::serialize(['a', 'b', 'c'], 'header', 'array'))->toBe('a,b,c');
});

test('header empty array joins to empty string', function (): void {
    expect(ValueSerializer::serialize([], 'header', 'array'))->toBe('');
});

test('header array of integers stringifies and joins', function (): void {
    expect(ValueSerializer::serialize([1, 2, 3], 'header', 'array'))->toBe('1,2,3');
});

// -- cookie location --

test('cookie string returns as is', function (): void {
    expect(ValueSerializer::serialize('hello', 'cookie', 'string'))->toBe('hello');
});

test('cookie null returns empty string', function (): void {
    expect(ValueSerializer::serialize(null, 'cookie', 'string'))->toBe('');
});

// -- form location --

test('form null returns empty string', function (): void {
    expect(ValueSerializer::serialize(null, 'form', 'string'))->toBe('');
});

test('form string returns as is', function (): void {
    expect(ValueSerializer::serialize('hello', 'form', 'string'))->toBe('hello');
});

test('form integer returns string', function (): void {
    expect(ValueSerializer::serialize(42, 'form', 'integer'))->toBe('42');
});

test('form boolean true returns true', function (): void {
    expect(ValueSerializer::serialize(true, 'form', 'boolean'))->toBe('true');
});

test('form boolean false returns false', function (): void {
    expect(ValueSerializer::serialize(false, 'form', 'boolean'))->toBe('false');
});

// -- serializeStyled: matrix style --

test('matrix scalar returns semicolon prefixed name value', function (): void {
    expect(ValueSerializer::serializeStyled('color', 'blue', 'path', 'string', null, 'matrix', true))->toBe(';color=blue');
});

test('matrix array with explode false joins with comma', function (): void {
    expect(ValueSerializer::serializeStyled('color', ['blue', 'black'], 'path', 'array', null, 'matrix', false))->toBe(';color=blue,black');
});

test('matrix array with explode true repeats name', function (): void {
    expect(ValueSerializer::serializeStyled('color', ['blue', 'black'], 'path', 'array', null, 'matrix', true))->toBe(';color=blue;color=black');
});

test('matrix null returns empty string', function (): void {
    expect(ValueSerializer::serializeStyled('color', null, 'path', 'string', null, 'matrix', true))->toBe('');
});

// -- serializeStyled: label style --

test('label scalar returns dot prefixed value', function (): void {
    expect(ValueSerializer::serializeStyled('color', 'blue', 'path', 'string', null, 'label', true))->toBe('.blue');
});

test('label array with explode false joins with comma', function (): void {
    expect(ValueSerializer::serializeStyled('color', ['blue', 'black'], 'path', 'array', null, 'label', false))->toBe('.blue,black');
});

test('label array with explode true joins with dot', function (): void {
    expect(ValueSerializer::serializeStyled('color', ['blue', 'black'], 'path', 'array', null, 'label', true))->toBe('.blue.black');
});

test('label null returns empty string', function (): void {
    expect(ValueSerializer::serializeStyled('color', null, 'path', 'string', null, 'label', true))->toBe('');
});

// -- serializeStyled: spaceDelimited style --

test('space delimited array joins with space', function (): void {
    expect(ValueSerializer::serializeStyled('color', ['blue', 'black'], 'query', 'array', null, 'spaceDelimited', false))->toBe('blue black');
});

test('space delimited scalar returns stringified value', function (): void {
    expect(ValueSerializer::serializeStyled('color', 'blue', 'query', 'string', null, 'spaceDelimited', false))->toBe('blue');
});

// -- serializeStyled: pipeDelimited style --

test('pipe delimited array joins with pipe', function (): void {
    expect(ValueSerializer::serializeStyled('color', ['blue', 'black'], 'query', 'array', null, 'pipeDelimited', false))->toBe('blue|black');
});

test('pipe delimited scalar returns stringified value', function (): void {
    expect(ValueSerializer::serializeStyled('color', 'blue', 'query', 'string', null, 'pipeDelimited', false))->toBe('blue');
});

// -- serializeStyled: form style with explode --

test('form style array with explode false joins with comma', function (): void {
    expect(ValueSerializer::serializeStyled('color', ['blue', 'black'], 'query', 'array', null, 'form', false))->toBe('blue,black');
});

test('form style array with explode true returns list', function (): void {
    expect(ValueSerializer::serializeStyled('color', ['blue', 'black'], 'query', 'array', null, 'form', true))->toBe(['blue', 'black']);
});

test('form style scalar with explode true returns string not list', function (): void {
    expect(ValueSerializer::serializeStyled('color', 'blue', 'query', 'string', null, 'form', true))->toBe('blue');
});

test('form style single element array with explode true returns list', function (): void {
    expect(ValueSerializer::serializeStyled('color', ['blue'], 'query', 'array', null, 'form', true))->toBe(['blue']);
});

test('form style null returns null for query', function (): void {
    expect(ValueSerializer::serializeStyled('color', null, 'query', 'string', null, 'form', true))->toBeNull();
});

// -- serializeStyled: simple style backward compatibility --

test('simple scalar returns stringified value', function (): void {
    expect(ValueSerializer::serializeStyled('id', '5', 'path', 'string', null, 'simple', false))->toBe('5');
});

test('simple array joins with comma', function (): void {
    expect(ValueSerializer::serializeStyled('id', ['3', '4', '5'], 'path', 'array', null, 'simple', false))->toBe('3,4,5');
});

test('simple null returns empty string', function (): void {
    expect(ValueSerializer::serializeStyled('id', null, 'path', 'string', null, 'simple', true))->toBe('');
});

test('simple scalar path is url encoded', function (): void {
    // Gap W1: simple-style path values must be percent-encoded so reserved
    // characters (space, '/', '?', '#') don't leak into the URL.
    expect(ValueSerializer::serializeStyled('id', 'hello world', 'path', 'string', null, 'simple', false))->toBe('hello%20world');
});

// -- serializeStyled: null style falls back to location default --

test('null style behaves like simple for path', function (): void {
    expect(ValueSerializer::serializeStyled('id', '5', 'path', 'string', null, null, false))->toBe('5');
});

test('empty style falls back', function (): void {
    expect(ValueSerializer::serializeStyled('id', '5', 'path', 'string', null, '', false))->toBe('5');
});

// -- serializeDeepObject --

test('deep object basic map returns bracketed keys', function (): void {
    $result = ValueSerializer::serializeDeepObject('filter', ['color' => 'blue', 'size' => 'large']);
    expect($result['filter[color]'])->toBe('blue');
    expect($result['filter[size]'])->toBe('large');
});

test('deep object null returns empty array', function (): void {
    $result = ValueSerializer::serializeDeepObject('filter', null);
    expect($result)->toBe([]);
});

// -- path encoding parity --
// Cross-language parity tests for path-segment percent-encoding.
// Every SDK must produce identical encoded strings for these inputs.

test('path encoding parity ascii safe pass through', function (): void {
    expect(ValueSerializer::serialize('abc123', 'path', 'string'))->toBe('abc123');
});

test('path encoding parity space encoded', function (): void {
    expect(ValueSerializer::serialize('a b', 'path', 'string'))->toBe('a%20b');
});

test('path encoding parity slash encoded', function (): void {
    expect(ValueSerializer::serialize('a/b', 'path', 'string'))->toBe('a%2Fb');
});

test('path encoding parity question mark encoded', function (): void {
    expect(ValueSerializer::serialize('a?b', 'path', 'string'))->toBe('a%3Fb');
});

test('path encoding parity hash encoded', function (): void {
    expect(ValueSerializer::serialize('a#b', 'path', 'string'))->toBe('a%23b');
});

test('path encoding parity comma preserved', function (): void {
    expect(ValueSerializer::serialize('a,b', 'path', 'string'))->toBe('a,b');
});

test('path encoding parity colon preserved', function (): void {
    expect(ValueSerializer::serialize('a:b', 'path', 'string'))->toBe('a:b');
});

test('path encoding parity plus preserved', function (): void {
    expect(ValueSerializer::serialize('a+b', 'path', 'string'))->toBe('a+b');
});

test('path encoding parity unicode encoded', function (): void {
    expect(ValueSerializer::serialize('日本', 'path', 'string'))->toBe('%E6%97%A5%E6%9C%AC');
});

test('path encoding parity empty string preserved', function (): void {
    expect(ValueSerializer::serialize('', 'path', 'string'))->toBe('');
});

test('path encoding parity null returns empty', function (): void {
    expect(ValueSerializer::serialize(null, 'path', 'string'))->toBe('');
});

test('path encoding parity simple style encodes value', function (): void {
    expect(ValueSerializer::serializeStyled('color', 'a b', 'path', 'string', null, 'simple', false))->toBe('a%20b');
});

test('path encoding parity simple style array encodes each item', function (): void {
    expect(ValueSerializer::serializeStyled('color', ['a b', 'c?d'], 'path', 'array', null, 'simple', false))->toBe('a%20b,c%3Fd');
});

test('path array item with reserved char is percent encoded', function (): void {
    // Gap W1 regression: every per-item path value in a styled array
    // must be percent-encoded BEFORE being joined with the structural
    // separator. Otherwise '/', '?', '#', space leak into the URL.
    $items = ['a/b', 'c'];
    expect(ValueSerializer::serializeStyled('name', $items, 'path', 'array', null, 'simple', false))->toBe('a%2Fb,c');
    expect(ValueSerializer::serializeStyled('name', $items, 'path', 'array', null, 'label', true))->toBe('.a%2Fb.c');
    expect(ValueSerializer::serializeStyled('name', $items, 'path', 'array', null, 'matrix', false))->toBe(';name=a%2Fb,c');
});

test('encode path segment preserves oas sub delimiters', function (): void {
    // php-rawurlencode-overencodes: bare rawurlencode escapes the OAS
    // sub-delimiters (; = , : @ ! $ & ' ( ) * +) that the other 11 SDKs
    // keep literal in path segments. encodePathSegment must preserve them
    // while still encoding genuinely-reserved chars (space, /, ?, #).
    expect(ValueSerializer::encodePathSegment(';=,:@!$&\'()*+'))->toBe(';=,:@!$&\'()*+');
    expect(ValueSerializer::encodePathSegment('a b'))->toBe('a%20b');
    expect(ValueSerializer::encodePathSegment('a/b'))->toBe('a%2Fb');
    expect(ValueSerializer::encodePathSegment('a#b'))->toBe('a%23b');
});

test('styled path scalar preserves sub delimiters not over encoded', function (): void {
    // php-rawurlencode-overencodes regression on the styled per-item path.
    expect(ValueSerializer::serializeStyled('id', 'a,b', 'path', 'string', null, 'simple', false))->toBe('a,b');
    expect(ValueSerializer::serializeStyled('id', 'a:b', 'path', 'string', null, 'simple', false))->toBe('a:b');
    expect(ValueSerializer::serializeStyled('id', 'a@b', 'path', 'string', null, 'simple', false))->toBe('a@b');
});

test('path encoding parity matrix style encodes value', function (): void {
    expect(ValueSerializer::serializeStyled('color', 'a b', 'path', 'string', null, 'matrix', false))->toBe(';color=a%20b');
});

test('path encoding parity label style encodes value', function (): void {
    expect(ValueSerializer::serializeStyled('color', 'a b', 'path', 'string', null, 'label', false))->toBe('.a%20b');
});

test('path encoding parity query location not path encoded', function (): void {
    expect(ValueSerializer::serializeStyled('color', 'a b', 'query', 'string', null, 'form', false))->toBe('a b');
});

test('empty string path param throws', function (): void {
    // Gap W — empty-string path values silently produce malformed
    // URLs like `/pet//details`; reject at serialization time so
    // callers see the real error rather than a downstream 404.
    expect(fn (): string|array|null => ValueSerializer::serializeStyled('id', '', 'path', 'string', null, 'simple', false))
        ->toThrow(\InvalidArgumentException::class);
});

test('path value is encoded exactly once not double encoded', function (): void {
    // path-double-encoding: serializeStyled already percent-encodes the path
    // segment, so the operation template must NOT wrap it again. A space must
    // become %20 (never %2520) and a slash %2F (never %252F).
    $space = ValueSerializer::serializeStyled('id', 'a b', 'path', 'string', null, 'simple', false);
    expect($space)->toBe('a%20b');
    expect($space)->not->toContain('%2520');
    $slash = ValueSerializer::serializeStyled('id', 'a/b', 'path', 'string', null, 'simple', false);
    expect($slash)->toBe('a%2Fb');
    expect($slash)->not->toContain('%252F');
});

test('path date only at year boundary emits yyyy mm dd', function (): void {
    // UTC/midnight edge: a format: date value on the year boundary must emit a
    // bare YYYY-MM-DD with no time/offset suffix, matching the stringifyDate
    // UTC behaviour of Go/Node/Swift/Dart.
    $dt = new \DateTime('2024-12-31T00:00:00+00:00');
    expect(ValueSerializer::serialize($dt, 'path', '\\DateTime|date'))->toBe('2024-12-31');
});
