<?php

declare(strict_types=1);

use Zitadel\Client\Models\UserServiceListUsersResponse;
use Zitadel\Client\Models\UserServiceUser;
use Zitadel\Client\ObjectSerializer;

/*
 * Regression coverage for DsAwareObjectNormalizer.
 *
 * A model property typed `\Ds\Vector<UserServiceUser>` carries the inner
 * element type only in PHPDoc, using the short class name relative to the
 * file's namespace. The normalizer must resolve that short name to its FQCN
 * before hydrating; otherwise every list element stays a raw array and the
 * model accessors (`$user->userId`) silently return null.
 */

test('list response result vector hydrates inner model instances', function (): void {
    $json = json_encode([
        'result' => [
            ['userId' => 'user-1', 'username' => 'alice'],
            ['userId' => 'user-2', 'username' => 'bob'],
        ],
    ], JSON_THROW_ON_ERROR);

    $response = ObjectSerializer::deserialize($json, UserServiceListUsersResponse::class);

    expect($response)->toBeInstanceOf(UserServiceListUsersResponse::class);
    expect($response->result)->not->toBeNull();
    expect($response->result->count())->toBe(2);

    $ids = [];
    foreach ($response->result as $user) {
        expect($user)->toBeInstanceOf(UserServiceUser::class);
        $ids[] = $user->userId;
    }

    expect($ids)->toBe(['user-1', 'user-2']);
});
