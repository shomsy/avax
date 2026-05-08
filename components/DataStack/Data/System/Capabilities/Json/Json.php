<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Json;

use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\AverageValues;
use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\FindMaxValue;
use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\FindMinValue;
use Avax\Components\DataStack\Data\System\Capabilities\Aggregate\SumValues;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\DotPath;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\MutationGuard;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal\Pair;
use Avax\Components\DataStack\Data\System\Capabilities\DataPipeline\DataPipeline;
use Avax\Components\DataStack\Data\System\Capabilities\Search\ContainsValue;
use Avax\Components\DataStack\Data\System\Capabilities\Search\SearchValue;
use Avax\Components\DataStack\Data\System\Capabilities\Selection\HasValue;
use Avax\Components\DataStack\Data\System\Capabilities\Selection\ReadValueByPath;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ChunkValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ConvertCollectionToJson;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ConvertCollectionToXml;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\EachValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\FilterValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\FlattenValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\GroupValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\MapValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\PartitionValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\PullValue;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\PutValueByPath;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ReduceValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\RejectValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ReverseValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\ShuffleValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\SortValues;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\SortValuesBy;
use Avax\Components\DataStack\Data\System\Capabilities\Transform\UniqueValues;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidJson;
use JsonException;
use NoDiscard;

/**
 * Json — immutable JSON document manipulation.
 *
 * Shares the same method vocabulary as Arrhae and Collection
 * for consistent data manipulation across the AvaX data DSL.
 */
final readonly class Json
{
    use DataPipeline;

    private MutationGuard $mutationGuard;

    /**
     * @param array<array-key, mixed> $data
     */
    public function __construct(
        private array $data = [],
    ) {
        $this->mutationGuard = new MutationGuard();
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function data(): array
    {
        return $this->data;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    protected function createWithData(array $data): static
    {
        return new self(data: $data);
    }

    public static function decode(string $json): static
    {
        try {
            $decoded = json_decode(
                json       : $json,
                associative: true,
                flags      : JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            throw InvalidJson::malformed(previous: $e);
        }

        if (! is_array(value: $decoded)) {
            $decoded = [$decoded];
        }

        return new self(data: $decoded);
    }

    /**
     * @param iterable<array-key, mixed> $items
     */
    public static function make(iterable $items = []): static
    {
        $data = is_array(value: $items) ? $items : iterator_to_array(iterator: $items);

        return new self(data: $data);
    }

    public static function wrap(mixed $value): static
    {
        return match (true) {
            $value instanceof static => $value,
            is_array(value: $value)  => new self(data: $value),
            default                  => new self(data: [$value]),
        };
    }

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists(key: $key, array: $this->data)) {
            return $this->data[$key];
        }

        if (str_contains(haystack: $key, needle: '.')) {
            return new ReadValueByPath(items: $this->data)->get(path: $key, default: $default);
        }

        return $default;
    }

    public function has(string $key): bool
    {
        if (array_key_exists(key: $key, array: $this->data)) {
            return true;
        }

        return new HasValue(items: $this->data)->check(key: $key);
    }

    public function first(mixed $default = null): mixed
    {
        if ($this->data === []) {
            return $default;
        }

        $copy = $this->data;

        return reset(array: $copy);
    }

    public function last(mixed $default = null): mixed
    {
        if ($this->data === []) {
            return $default;
        }

        $copy = $this->data;

        return end(array: $copy);
    }

    #[NoDiscard]
    public function add(mixed $value): static
    {
        $this->mutationGuard->assertMutable();

        $data = $this->data;
        $data[] = $value;

        return new self(data: $data);
    }

    public function path(string $pointer): mixed
    {
        $dotPath = $this->jsonPointerToDotPath(pointer: $pointer);

        if ($dotPath === '') {
            return $this->data;
        }

        return new DotPath(path: $dotPath)->getValue(items: $this->data);
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function merge(array $data): static
    {
        return $this->createWithData(data: array_merge($this->data(), $data));
    }

    #[NoDiscard]
    public function set(string $key, mixed $value): static
    {
        $this->mutationGuard->assertMutable();

        if (str_contains(haystack: $key, needle: '.')) {
            return new self(
                data: new PutValueByPath(items: $this->data)->put(path: $key, value: $value),
            );
        }

        $data       = $this->data;
        $data[$key] = $value;

        return new self(data: $data);
    }

    #[NoDiscard]
    public function forget(string $key): static
    {
        $this->mutationGuard->assertMutable();

        if (! array_key_exists(key: $key, array: $this->data) && str_contains(haystack: $key, needle: '.')) {
            $data = $this->data;
            new DotPath(path: $key)->unsetValue(items: $data);

            return new self(data: $data);
        }

        $data = $this->data;
        unset($data[$key]);

        return new self(data: $data);
    }

    public function pull(string $key): Pair
    {
        $this->mutationGuard->assertMutable();

        [$value, $data] = new PullValue(items: $this->data)->pull(key: $key);

        return new Pair(
            first : $value,
            second: $data === $this->data ? $this : new self(data: $data),
        );
    }

    #[NoDiscard]
    public function map(callable $callback): static
    {
        return new self(
            data: new MapValues(items: $this->data)->map(callback: $callback),
        );
    }

    #[NoDiscard]
    public function filter(callable $callback): static
    {
        return new self(
            data: new FilterValues(items: $this->data)->filter(callback: $callback),
        );
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return new ReduceValues(items: $this->data)->reduce(callback: $callback, initial: $initial);
    }

    #[NoDiscard]
    public function reject(callable $callback): static
    {
        return new self(
            data: new RejectValues(items: $this->data)->reject(callback: $callback),
        );
    }

    #[NoDiscard]
    public function flatten(int $depth = PHP_INT_MAX): static
    {
        return new self(
            data: new FlattenValues(items: $this->data)->flatten(depth: max(1, $depth)),
        );
    }

    #[NoDiscard]
    public function each(callable $callback): static
    {
        return new self(
            data: new EachValues(items: $this->data)->each(callback: $callback),
        );
    }

    public function sum(string|callable $key): int|float
    {
        return new SumValues(items: $this->data)->sum(key: $key);
    }

    public function average(string|callable $key): float
    {
        return new AverageValues(items: $this->data)->average(key: $key);
    }

    public function min(string|callable $key): mixed
    {
        return new FindMinValue(items: $this->data)->min(key: $key);
    }

    public function max(string|callable $key): mixed
    {
        return new FindMaxValue(items: $this->data)->max(key: $key);
    }

    #[NoDiscard]
    public function sort(?callable $callback = null): static
    {
        return new self(
            data: new SortValues(items: $this->data)->sort(callback: $callback),
        );
    }

    #[NoDiscard]
    public function sortBy(string|callable $key, bool $descending = false): static
    {
        return new self(
            data: new SortValuesBy(items: $this->data)->sortBy(key: $key, options: SORT_REGULAR, descending: $descending),
        );
    }

    #[NoDiscard]
    public function reverse(): static
    {
        return new self(
            data: new ReverseValues(items: $this->data)->reverse(),
        );
    }

    #[NoDiscard]
    public function shuffle(): static
    {
        return new self(
            data: new ShuffleValues(items: $this->data)->shuffle(),
        );
    }

    #[NoDiscard]
    public function unique(): static
    {
        return new self(
            data: new UniqueValues(items: $this->data)->unique(),
        );
    }

    #[NoDiscard]
    public function chunk(int $size): static
    {
        return new self(
            data: new ChunkValues(items: $this->data)->chunk(size: $size),
        );
    }

    /**
     * @return array<array-key, list<mixed>>
     */
    public function groupBy(string|callable $key): array
    {
        return new GroupValues(items: $this->data)->group(key: $key);
    }

    /**
     * @return array{static, static}
     */
    public function partition(callable $callback): array
    {
        [$pass, $fail] = new PartitionValues(items: $this->data)->partition(callback: $callback);

        return [new self(data: $pass), new self(data: $fail)];
    }

    public function contains(mixed $value): bool
    {
        return new ContainsValue(items: $this->data)->contains(value: $value);
    }

    public function search(mixed $value): int|false
    {
        return new SearchValue(items: $this->data)->search(value: $value);
    }

    public function count(): int
    {
        return count(value: $this->data);
    }

    public function isEmpty(): bool
    {
        return $this->data === [];
    }

    public function isNotEmpty(): bool
    {
        return $this->data !== [];
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function toJson(int $flags = 0): string
    {
        return new ConvertCollectionToJson(items: $this->data)->toJson(flags: $flags);
    }

    public function pretty(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): string
    {
        return new ConvertCollectionToJson(items: $this->data)->toJson(flags: $flags);
    }

    public function encode(): string
    {
        return $this->toJson();
    }

    public function toXml(string $rootElement = 'root'): string
    {
        return new ConvertCollectionToXml(items: $this->data)->toXml(rootElement: $rootElement);
    }

    /**
     * Basic schema validation: checks required keys and expected types.
     *
     * @param array<string, string|list<string>> $schema
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate(array $schema): array
    {
        $errors  = [];
        $typeMap = [
            'string'  => 'is_string',
            'int'     => 'is_int',
            'float'   => 'is_float',
            'bool'    => 'is_bool',
            'array'   => 'is_array',
            'null'    => 'is_null',
            'numeric' => 'is_numeric',
            'object'  => 'is_object',
        ];

        foreach ($schema as $key => $expectedTypes) {
            $value         = $this->resolveForValidation(key: $key);
            $expectedTypes = is_array(value: $expectedTypes) ? $expectedTypes : [$expectedTypes];

            if ($value === null && ! in_array(needle: 'null', haystack: $expectedTypes, strict: true)) {
                $errors[] = "Missing required key: {$key}";

                continue;
            }

            if ($value !== null && ! $this->matchesAnyType(value: $value, types: $expectedTypes, map: $typeMap)) {
                $actualType = get_debug_type(value: $value);
                $expected   = implode('|', $expectedTypes);
                $errors[]   = "Key '{$key}' expected {$expected}, got {$actualType}";
            }
        }

        return [
            'valid'  => $errors === [],
            'errors' => $errors,
        ];
    }

    private function resolveForValidation(string $key): mixed
    {
        if (array_key_exists(key: $key, array: $this->data)) {
            return $this->data[$key];
        }

        if (str_contains(haystack: $key, needle: '.')) {
            return new DotPath(path: $key)->getValue(items: $this->data);
        }

        return null;
    }

    /**
     * @param list<string>          $types
     * @param array<string, string> $map
     */
    private function matchesAnyType(mixed $value, array $types, array $map): bool
    {
        foreach ($types as $type) {
            $checker = $map[$type] ?? null;

            if ($checker !== null && is_callable(value: $checker) && call_user_func($checker, $value)) {
                return true;
            }
        }

        return false;
    }

    private function jsonPointerToDotPath(string $pointer): string
    {
        $pointer = ltrim(string: $pointer, characters: '/');

        if ($pointer === '') {
            return '';
        }

        $segments = explode(separator: '/', string: $pointer);

        return implode(
            separator: '.',
            array    : array_map(
                callback: static fn (string $segment): string => str_replace(
                    search : ['~1', '~0'],
                    replace: ['/', '~'],
                    subject: $segment,
                ),
                array   : $segments,
            ),
        );
    }

    public function toImmutable(): static
    {
        return $this->lock();
    }

    public function lock(): static
    {
        $clone = new self(data: $this->data);
        $clone->mutationGuard->lock();

        return $clone;
    }

    public function isLocked(): bool
    {
        return $this->mutationGuard->isLocked();
    }
}
