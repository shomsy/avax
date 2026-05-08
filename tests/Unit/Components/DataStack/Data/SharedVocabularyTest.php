<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Arrhae;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Contract test: Arrhae, Collection, and Json produce identical results
 * for shared methods in the AvaX data DSL vocabulary.
 */
final class SharedVocabularyTest extends TestCase
{
    // -- Factory test data --------------------------------------------------------

    /**
     * @return array<string, array{0: array<array-key, mixed>}>
     */
    public static function arrayTestData() : array
    {
        return [
            'simple'    => [['name' => 'AvaX', 'version' => 1]],
            'nested'    => [['user' => ['name' => 'John', 'address' => ['city' => 'NYC']]]],
            'flat_list' => [['a' => 1, 'b' => 2, 'c' => 3]],
        ];
    }

    // -- Shared: all() ------------------------------------------------------------

    /**
     * @param array<array-key, mixed> $data
     */
    #[DataProvider('arrayTestData')]
    public function test_shared_all(array $data) : void
    {
        $arrhae     = Arrhae::make($data);
        $collection = Collection::make($data);
        $json       = Json::make($data);

        self::assertSame($data, $arrhae->all());
        self::assertSame($data, $collection->all());
        self::assertSame($data, $json->all());
    }

    // -- Shared: get() ------------------------------------------------------------

    public function test_shared_get_simple() : void
    {
        $data = ['name' => 'AvaX', 'version' => 1];

        self::assertSame('AvaX', Arrhae::make($data)->get(key: 'name'));
        self::assertSame('AvaX', Collection::make($data)->get(key: 'name'));
        self::assertSame('AvaX', Json::make($data)->get(key: 'name'));
    }

    public function test_shared_get_missing_default() : void
    {
        $data = [];

        self::assertSame('fallback', Arrhae::make($data)->get(key: 'missing', default: 'fallback'));
        self::assertSame('fallback', Collection::make($data)->get(key: 'missing', default: 'fallback'));
        self::assertSame('fallback', Json::make($data)->get(key: 'missing', default: 'fallback'));
    }

    public function test_shared_get_dot_path() : void
    {
        $data = ['user' => ['address' => ['city' => 'NYC']]];

        self::assertSame('NYC', Arrhae::make($data)->get(key: 'user.address.city'));
        self::assertSame('NYC', Collection::make($data)->get(key: 'user.address.city'));
        self::assertSame('NYC', Json::make($data)->get(key: 'user.address.city'));
    }

    // -- Shared: has() ------------------------------------------------------------

    public function test_shared_has() : void
    {
        $data = ['name' => 'AvaX'];

        self::assertTrue(Arrhae::make($data)->has(key: 'name'));
        self::assertTrue(Collection::make($data)->has(key: 'name'));
        self::assertTrue(Json::make($data)->has(key: 'name'));

        self::assertFalse(Arrhae::make($data)->has(key: 'missing'));
        self::assertFalse(Collection::make($data)->has(key: 'missing'));
        self::assertFalse(Json::make($data)->has(key: 'missing'));
    }

    public function test_shared_has_dot_path() : void
    {
        $data = ['user' => ['name' => 'John']];

        self::assertTrue(Arrhae::make($data)->has(key: 'user.name'));
        self::assertTrue(Collection::make($data)->has(key: 'user.name'));
        self::assertTrue(Json::make($data)->has(key: 'user.name'));
    }

    // -- Shared: set() ------------------------------------------------------------

    public function test_shared_set() : void
    {
        $data = ['name' => 'AvaX'];

        $a = Arrhae::make($data)->set(key: 'version', value: 1);
        $c = Collection::make($data)->set(key: 'version', value: 1);
        $j = Json::make($data)->set(key: 'version', value: 1);

        self::assertSame(['name' => 'AvaX', 'version' => 1], $a->all());
        self::assertSame(['name' => 'AvaX', 'version' => 1], $c->all());
        self::assertSame(['name' => 'AvaX', 'version' => 1], $j->all());
    }

    // -- Shared: forget() ---------------------------------------------------------

    public function test_shared_forget() : void
    {
        $data = ['name' => 'AvaX', 'version' => 1];

        $a = Arrhae::make($data)->forget(key: 'version');
        $c = Collection::make($data)->forget(key: 'version');
        $j = Json::make($data)->forget(key: 'version');

        self::assertSame(['name' => 'AvaX'], $a->all());
        self::assertSame(['name' => 'AvaX'], $c->all());
        self::assertSame(['name' => 'AvaX'], $j->all());
    }

    // -- Shared: merge() ----------------------------------------------------------

    public function test_shared_merge() : void
    {
        $data = ['name' => 'AvaX'];

        $a = Arrhae::make($data)->merge(items: ['version' => 1]);
        $c = Collection::make($data)->merge(items: ['version' => 1]);
        $j = Json::make($data)->merge(data: ['version' => 1]);

        self::assertSame(['name' => 'AvaX', 'version' => 1], $a->all());
        self::assertSame(['name' => 'AvaX', 'version' => 1], $c->all());
        self::assertSame(['name' => 'AvaX', 'version' => 1], $j->all());
    }

    // -- Shared: count() ----------------------------------------------------------

    public function test_shared_count() : void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        self::assertSame(3, Arrhae::make($data)->count());
        self::assertSame(3, Collection::make($data)->count());
        self::assertSame(3, Json::make($data)->count());
    }

    // -- Shared: isEmpty / isNotEmpty ---------------------------------------------

    public function test_shared_is_empty() : void
    {
        self::assertTrue(Arrhae::make([])->isEmpty());
        self::assertTrue(Collection::make([])->isEmpty());
        self::assertTrue(Json::make([])->isEmpty());

        self::assertFalse(Arrhae::make([1])->isEmpty());
        self::assertFalse(Collection::make([1])->isEmpty());
        self::assertFalse(Json::make([1])->isEmpty());
    }

    public function test_shared_is_not_empty() : void
    {
        self::assertFalse(Arrhae::make([])->isNotEmpty());
        self::assertFalse(Collection::make([])->isNotEmpty());
        self::assertFalse(Json::make([])->isNotEmpty());

        self::assertTrue(Arrhae::make([1])->isNotEmpty());
        self::assertTrue(Collection::make([1])->isNotEmpty());
        self::assertTrue(Json::make([1])->isNotEmpty());
    }

    // -- Shared: only() -----------------------------------------------------------

    public function test_shared_only() : void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        self::assertSame(['a' => 1, 'b' => 2], Arrhae::make($data)->only(keys: ['a', 'b'])->all());
        self::assertSame(['a' => 1, 'b' => 2], Collection::make($data)->only(keys: ['a', 'b'])->all());
        self::assertSame(['a' => 1, 'b' => 2], Json::make($data)->only(keys: ['a', 'b'])->all());
    }

    // -- Shared: except() ---------------------------------------------------------

    public function test_shared_except() : void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        self::assertSame(['b' => 2, 'c' => 3], Arrhae::make($data)->except(keys: ['a'])->all());
        self::assertSame(['b' => 2, 'c' => 3], Collection::make($data)->except(keys: ['a'])->all());
        self::assertSame(['b' => 2, 'c' => 3], Json::make($data)->except(keys: ['a'])->all());
    }

    // -- Shared: keys() -----------------------------------------------------------

    public function test_shared_keys() : void
    {
        $data = ['foo' => 'bar', 'baz' => 'qux'];

        self::assertSame(['foo', 'baz'], Arrhae::make($data)->keys());
        self::assertSame(['foo', 'baz'], Collection::make($data)->keys());
        self::assertSame(['foo', 'baz'], Json::make($data)->keys());
    }

    // -- Shared: toJson() ---------------------------------------------------------

    public function test_shared_to_json() : void
    {
        $data = ['name' => 'AvaX'];

        self::assertSame('{"name":"AvaX"}', Arrhae::make($data)->toJson());
        self::assertSame('{"name":"AvaX"}', Collection::make($data)->toJson());
        self::assertSame('{"name":"AvaX"}', Json::make($data)->toJson());
    }

    // -- Shared: toArray() --------------------------------------------------------

    public function test_shared_to_array() : void
    {
        $data = ['name' => 'AvaX'];

        self::assertSame($data, Arrhae::make($data)->toArray());
        self::assertSame($data, Collection::make($data)->toArray());
        self::assertSame($data, Json::make($data)->toArray());
    }

    // -- Shared: lock / isLocked --------------------------------------------------

    public function test_shared_lock() : void
    {
        self::assertFalse(Arrhae::make([])->isLocked());
        self::assertFalse(Collection::make([])->isLocked());
        self::assertFalse(Json::make([])->isLocked());

        self::assertTrue(Arrhae::make([])->lock()->isLocked());
        self::assertTrue(Collection::make([])->lock()->isLocked());
        self::assertTrue(Json::make([])->lock()->isLocked());
    }
}
