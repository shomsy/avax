<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections;

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Convert\ConvertCollectionToJson;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Exceptions\InvalidJson;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Mutability\MutationGuard;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Paths\DotPath;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Read\HasValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Read\ReadValueByPath;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Write\ForgetValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Write\PutValueByPath;
use JsonException;
use NoDiscard;

/**
 * Json — immutable JSON document manipulation.
 *
 * Shares the same method vocabulary as Arrhae and Collection
 * for consistent data manipulation across the AvaX data DSL.
 *
 * Unlike Collection, Json does not implement CollectionInterface —
 * it is a JSON document wrapper, not an item pipeline.
 */
final readonly class Json
{
    private MutationGuard $mutationGuard;

    /**
     * @param array<array-key, mixed> $data
     */
    public function __construct(
        private array $data = [],
    )
    {
        $this->mutationGuard = new MutationGuard();
    }

    // -- Factories ----------------------------------------------------------------

    /**
     * Parse a JSON string into a Json document.
     *
     * @throws InvalidJson
     */
    public static function decode(string $json) : static
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
    public static function make(iterable $items = []) : static
    {
        $data = is_array(value: $items) ? $items : iterator_to_array(iterator: $items);

        return new self(data: $data);
    }

    public static function wrap(mixed $value) : static
    {
        return match (true) {
            $value instanceof static => $value,
            is_array(value: $value)  => new self(data: $value),
            default                  => new self(data: [$value]),
        };
    }

    // -- Access -------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        if (array_key_exists(key: $key, array: $this->data)) {
            return $this->data[$key];
        }

        if (str_contains(haystack: $key, needle: '.')) {
            return new ReadValueByPath(items: $this->data)->get(path: $key, default: $default);
        }

        return $default;
    }

    public function has(string $key) : bool
    {
        if (array_key_exists(key: $key, array: $this->data)) {
            return true;
        }

        return new HasValue(items: $this->data)->check(key: $key);
    }

    /**
     * Access a value using JSON Pointer (RFC 6901) syntax.
     *
     * Converts /foo/bar to foo.bar dot-path notation.
     */
    public function path(string $pointer) : mixed
    {
        $dotPath = $this->jsonPointerToDotPath(pointer: $pointer);

        if ($dotPath === '') {
            return $this->data;
        }

        return new DotPath(path: $dotPath)->getValue(items: $this->data);
    }

    // -- Mutation (returns new instance) ------------------------------------------

    private function jsonPointerToDotPath(string $pointer) : string
    {
        // RFC 6901: remove leading '/', unescape ~1 -> /, ~0 -> ~
        $pointer = ltrim(string: $pointer, characters: '/');

        if ($pointer === '') {
            return '';
        }

        $segments = explode(separator: '/', string: $pointer);

        return implode(
            separator: '.',
            array    : array_map(
                           callback: static fn (string $segment) : string => str_replace(
                               search : ['~1', '~0'],
                               replace: ['/', '~'],
                               subject: $segment,
                           ),
                           array   : $segments,
                       ),
        );
    }

    #[NoDiscard]
    public function set(string $key, mixed $value) : static
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
    public function forget(string $key) : static
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

    // -- Selection ----------------------------------------------------------------

    /**
     * @param array<string, mixed> $data
     */
    #[NoDiscard]
    public function merge(array $data) : static
    {
        return new self(data: array_merge($this->data, $data));
    }

    /**
     * @param list<string> $keys
     */
    public function only(array $keys) : static
    {
        $filtered = array_filter(
            array   : $this->data,
            callback: static fn (mixed $_, mixed $key) : bool => in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH,
        );

        return new self(data: $filtered);
    }

    // -- Info ---------------------------------------------------------------------

    /**
     * @param list<string> $keys
     */
    public function except(array $keys) : static
    {
        $filtered = array_filter(
            array   : $this->data,
            callback: static fn (mixed $_, mixed $key) : bool => ! in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH,
        );

        return new self(data: $filtered);
    }

    /**
     * @return list<string>
     */
    public function keys() : array
    {
        return array_keys(array: $this->data);
    }

    public function count() : int
    {
        return count(value: $this->data);
    }

    public function isEmpty() : bool
    {
        return $this->data === [];
    }

    // -- Conversion ---------------------------------------------------------------

    public function isNotEmpty() : bool
    {
        return $this->data !== [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return $this->data;
    }

    /**
     * Return a pretty-printed JSON string.
     */
    public function pretty(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : string
    {
        return new ConvertCollectionToJson(items: $this->data)->toJson(flags: $flags);
    }

    public function toJson(int $flags = 0) : string
    {
        return new ConvertCollectionToJson(items: $this->data)->toJson(flags: $flags);
    }

    // -- Validation ---------------------------------------------------------------

    public function encode() : string
    {
        return $this->toJson();
    }

    // -- Immutability -------------------------------------------------------------

    /**
     * Basic schema validation: checks required keys and expected types.
     *
     * Schema shape: ['key' => 'string|int|float|bool|array|object|null', ...]
     * Supports dot-paths for nested keys.
     *
     * @param array<string, string|list<string>> $schema
     *
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate(array $schema) : array
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

    private function resolveForValidation(string $key) : mixed
    {
        if (array_key_exists(key: $key, array: $this->data)) {
            return $this->data[$key];
        }

        if (str_contains(haystack: $key, needle: '.')) {
            return new DotPath(path: $key)->getValue(items: $this->data);
        }

        return null;
    }

    // -- Internals ----------------------------------------------------------------

    /**
     * @param list<string>          $types
     * @param array<string, string> $map
     */
    private function matchesAnyType(mixed $value, array $types, array $map) : bool
    {
        foreach ($types as $type) {
            $checker = $map[$type] ?? null;

            if ($checker !== null && is_callable(value: $checker) && call_user_func($checker, $value)) {
                return true;
            }
        }

        return false;
    }

    public function lock() : static
    {
        $clone = new self(data: $this->data);
        $clone->mutationGuard->lock();

        return $clone;
    }

    public function isLocked() : bool
    {
        return $this->mutationGuard->isLocked();
    }
}
