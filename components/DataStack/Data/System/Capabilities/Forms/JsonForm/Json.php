<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Forms\JsonForm;

use Avax\Components\DataStack\Data\System\Capabilities\Forms\ArrayForm\Arrhae;
use Avax\Components\DataStack\Data\System\Capabilities\Lenses\DataPath\DotPath;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Functional\Pair;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidJson;
use Avax\Components\DataStack\Data\System\Foundation\Mutability\MutationGuard;
use JsonException;
use NoDiscard;

/**
 * Json — JSON document DSL over Arrhae.
 *
 * Composes Arrhae internally for all decoded document pipeline operations.
 * Json owns: decode, encode, pretty, validate, JSON Pointer access.
 */
final readonly class Json
{
    private MutationGuard $mutationGuard;

    public function __construct(
        private Arrhae $arrhae,
    ) {
        $this->mutationGuard = new MutationGuard();
    }

    // -- Factories ---------------------------------------------------------------

    /**
     * Decode a JSON string into a Json instance.
     *
     * Scalar JSON roots (string, number, bool, null) are silently wrapped
     * as a single-element array `[0 => $scalar]` so that the internal
     * Arrhae engine always receives array-backed data.
     */
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

        return new self(arrhae: Arrhae::make(items: $decoded));
    }

    /**
     * @param iterable<array-key, mixed> $items
     */
    public static function make(iterable $items = []): static
    {
        return new self(arrhae: Arrhae::make(items: $items));
    }

    public static function wrap(mixed $value): static
    {
        return match (true) {
            $value instanceof static => $value,
            $value instanceof Arrhae => new self(arrhae: $value),
            is_array(value: $value)  => new self(arrhae: Arrhae::make(items: $value)),
            default                  => new self(arrhae: Arrhae::wrap(value: $value)),
        };
    }

    // -- JSON-specific -----------------------------------------------------------

    public function path(string $pointer): mixed
    {
        $dotPath = $this->jsonPointerToDotPath(pointer: $pointer);

        if ($dotPath === '') {
            return $this->arrhae->all();
        }

        return (new DotPath(path: $dotPath))->getValue(items: $this->arrhae->all());
    }

    /**
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
            $value = $this->resolveForValidation(key: $key);
            $expectedTypes = is_array(value: $expectedTypes) ? $expectedTypes : [$expectedTypes];

            if ($value === null && ! in_array(needle: 'null', haystack: $expectedTypes, strict: true)) {
                $errors[] = "Missing required key: {$key}";
                continue;
            }

            if ($value !== null && ! $this->matchesAnyType(value: $value, types: $expectedTypes, map: $typeMap)) {
                $actualType = get_debug_type(value: $value);
                $expected = implode('|', $expectedTypes);
                $errors[] = "Key '{$key}' expected {$expected}, got {$actualType}";
            }
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    // -- Access (delegates to Arrhae) --------------------------------------------

    /** @return array<array-key, mixed> */
    public function all(): array { return $this->arrhae->all(); }
    public function get(string $key, mixed $default = null): mixed { return $this->arrhae->get(key: $key, default: $default); }
    public function has(string $key): bool { return $this->arrhae->has(key: $key); }
    public function first(mixed $default = null): mixed { return $this->arrhae->first(default: $default); }
    public function last(mixed $default = null): mixed { return $this->arrhae->last(default: $default); }
    public function count(): int { return $this->arrhae->count(); }
    public function isEmpty(): bool { return $this->arrhae->isEmpty(); }
    public function isNotEmpty(): bool { return $this->arrhae->isNotEmpty(); }

    // -- Mutation ----------------------------------------------------------------

    #[NoDiscard] public function add(mixed $value): static { $this->mutationGuard->assertMutable(); return new self(arrhae: $this->arrhae->add(value: $value)); }
    /** @param array<array-key, mixed> $data */
    public function merge(array $data): static { return new self(arrhae: $this->arrhae->merge(items: $data)); }
    #[NoDiscard] public function set(string $key, mixed $value): static { $this->mutationGuard->assertMutable(); return new self(arrhae: $this->arrhae->set(key: $key, value: $value)); }
    #[NoDiscard] public function forget(string $key): static { $this->mutationGuard->assertMutable(); return new self(arrhae: $this->arrhae->forget(key: $key)); }
    public function pull(string $key): Pair { return $this->arrhae->pull(key: $key); }

    // -- Pipeline (delegates to Arrhae) ------------------------------------------

    #[NoDiscard] public function map(callable $callback): static { return new self(arrhae: $this->arrhae->map(callback: $callback)); }
    #[NoDiscard] public function filter(callable $callback): static { return new self(arrhae: $this->arrhae->filter(callback: $callback)); }
    public function reduce(callable $callback, mixed $initial = null): mixed { return $this->arrhae->reduce(callback: $callback, initial: $initial); }
    #[NoDiscard] public function reject(callable $callback): static { return new self(arrhae: $this->arrhae->reject(callback: $callback)); }
    #[NoDiscard] public function flatten(int $depth = PHP_INT_MAX): static { return new self(arrhae: $this->arrhae->flatten(depth: $depth)); }
    #[NoDiscard] public function each(callable $callback): static { return new self(arrhae: $this->arrhae->each(callback: $callback)); }

    // -- Aggregate ---------------------------------------------------------------

    public function sum(string|callable $key): int|float { return $this->arrhae->sum(key: $key); }
    public function average(string|callable $key): float { return $this->arrhae->average(key: $key); }
    public function min(string|callable $key): mixed { return $this->arrhae->min(key: $key); }
    public function max(string|callable $key): mixed { return $this->arrhae->max(key: $key); }

    // -- Order -------------------------------------------------------------------

    #[NoDiscard]
    public function sort(callable|null $callback = null) : static
    {
        return new self(arrhae: $this->arrhae->sort(callback: $callback));
    }
    #[NoDiscard] public function sortBy(string|callable $key, bool $descending = false): static { return new self(arrhae: $this->arrhae->sortBy(key: $key, descending: $descending)); }
    #[NoDiscard] public function reverse(): static { return new self(arrhae: $this->arrhae->reverse()); }
    #[NoDiscard] public function shuffle(): static { return new self(arrhae: $this->arrhae->shuffle()); }
    #[NoDiscard] public function unique(): static { return new self(arrhae: $this->arrhae->unique()); }

    // -- Grouping ----------------------------------------------------------------

    #[NoDiscard] public function chunk(int $size): static { return new self(arrhae: $this->arrhae->chunk(size: $size)); }
    /** @return array<array-key, list<mixed>> */
    public function groupBy(string|callable $key): array { return $this->arrhae->groupBy(key: $key); }
    /** @return array{static, static} */
    public function partition(callable $callback): array { [$p, $f] = $this->arrhae->partition(callback: $callback); return [new self(arrhae: $p), new self(arrhae: $f)]; }

    // -- Search ------------------------------------------------------------------

    public function contains(mixed $value): bool { return $this->arrhae->contains(value: $value); }
    public function search(mixed $value): int|false { return $this->arrhae->search(value: $value); }

    // -- Selection ---------------------------------------------------------------

    /** @param list<int|string> $keys */
    public function only(array $keys): static { return new self(arrhae: $this->arrhae->only(keys: $keys)); }
    /** @param list<int|string> $keys */
    public function except(array $keys): static { return new self(arrhae: $this->arrhae->except(keys: $keys)); }
    /** @return list<mixed> */
    public function pluck(string|callable $key): array { return $this->arrhae->pluck(key: $key); }

    // -- Query / Filtering -------------------------------------------------------

    public function where(string $key, mixed $value): static { return new self(arrhae: $this->arrhae->where(key: $key, value: $value)); }
    /** @param list<mixed> $values */
    public function whereIn(string $key, array $values): static { return new self(arrhae: $this->arrhae->whereIn(key: $key, values: $values)); }
    /** @param array{mixed, mixed} $range */
    public function whereBetween(string $key, array $range): static { return new self(arrhae: $this->arrhae->whereBetween(key: $key, range: $range)); }
    public function whereNull(string $key): static { return new self(arrhae: $this->arrhae->whereNull(key: $key)); }
    public function whereNotNull(string $key): static { return new self(arrhae: $this->arrhae->whereNotNull(key: $key)); }

    // -- Keying ------------------------------------------------------------------

    public function keyBy(string|callable $key): static { return new self(arrhae: $this->arrhae->keyBy(key: $key)); }

    // -- Set Algebra -------------------------------------------------------------

    public function flip(): static { return new self(arrhae: $this->arrhae->flip()); }
    /** @param array<array-key, mixed> $items */
    public function union(array $items): static { return new self(arrhae: $this->arrhae->union(items: $items)); }
    /** @param array<array-key, mixed> $items */
    public function diff(array $items): static { return new self(arrhae: $this->arrhae->diff(items: $items)); }
    /** @param array<array-key, mixed> $items */
    public function intersect(array $items): static { return new self(arrhae: $this->arrhae->intersect(items: $items)); }

    // -- Info --------------------------------------------------------------------

    /** @return list<array-key> */
    public function keys(): array { return $this->arrhae->keys(); }
    public function values(): static { return new self(arrhae: $this->arrhae->values()); }

    // -- Conditional -------------------------------------------------------------

    public function tap(callable $callback): static { $callback($this); return $this; }
    public function when(bool $condition, callable $callback): static { return $condition ? ($callback($this) ?? $this) : $this; }
    public function unless(bool $condition, callable $callback): static { return $this->when(condition: ! $condition, callback: $callback); }

    // -- Conversion --------------------------------------------------------------

    /** @return array<array-key, mixed> */
    public function toArray(): array { return $this->arrhae->toArray(); }
    public function toJson(int $flags = 0): string { return $this->arrhae->toJson(flags: $flags); }
    public function pretty(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): string { return $this->arrhae->toJson(flags: $flags); }
    public function encode(): string { return $this->toJson(); }
    public function toXml(string $rootElement = 'root'): string { return $this->arrhae->toXml(rootElement: $rootElement); }

    // -- Immutability ------------------------------------------------------------

    public function toImmutable(): static { return $this->lock(); }
    public function lock(): static { $clone = new self(arrhae: $this->arrhae); $clone->mutationGuard->lock(); return $clone; }
    public function isLocked(): bool { return $this->mutationGuard->isLocked(); }

    // -- Internal ----------------------------------------------------------------

    private function resolveForValidation(string $key): mixed
    {
        $data = $this->arrhae->all();
        if (array_key_exists(key: $key, array: $data)) return $data[$key];
        if (str_contains(haystack: $key, needle: '.')) return (new DotPath(path: $key))->getValue(items: $data);
        return null;
    }

    /**
     * @param list<string> $types
     * @param array<string, string> $map
     */
    private function matchesAnyType(mixed $value, array $types, array $map): bool
    {
        foreach ($types as $type) {
            $checker = $map[$type] ?? null;
            if ($checker !== null && is_callable(value: $checker) && call_user_func($checker, $value)) return true;
        }
        return false;
    }

    private function jsonPointerToDotPath(string $pointer): string
    {
        $pointer = ltrim(string: $pointer, characters: '/');
        if ($pointer === '') return '';
        return implode(separator: '.', array: array_map(callback: static fn (string $s): string => str_replace(search: ['~1', '~0'], replace: ['/', '~'], subject: $s), array: explode(separator: '/', string: $pointer)));
    }
}
