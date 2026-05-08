<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections;

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Convert\ConvertCollectionToJson;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Create\MakeCollection;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Create\WrapValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Mutability\MutationGuard;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Paths\DotPath;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Read\HasValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Read\ReadValueByPath;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Write\AppendValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Write\ForgetValue;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Write\PutValueByPath;
use NoDiscard;

/**
 * Arrhae — raw array facade for simple array manipulation.
 *
 * Shares the same method vocabulary as Collection and Json
 * for consistent data manipulation across the AvaX data DSL.
 */
final readonly class Arrhae
{
    private MutationGuard $mutationGuard;

    public function __construct(
        private array $items = [],
    ) {
        $this->mutationGuard = new MutationGuard();
    }

    public static function from(iterable $items): static
    {
        return self::make(items: $items);
    }

    public static function make(iterable $items = []): static
    {
        return new self(items: new MakeCollection()->from(items: $items));
    }

    public static function wrap(mixed $value): static
    {
        return match (true) {
            $value instanceof static => $value,
            default => new self(items: new WrapValue()->intoArray(value: $value)),
        };
    }

    public function all(): array
    {
        return $this->items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists(key: $key, array: $this->items)) {
            return $this->items[$key];
        }

        if (str_contains(haystack: $key, needle: '.')) {
            return new ReadValueByPath(items: $this->items)->get(path: $key, default: $default);
        }

        return $default;
    }

    public function has(string $key): bool
    {
        if (array_key_exists(key: $key, array: $this->items)) {
            return true;
        }

        return new HasValue(items: $this->items)->check(key: $key);
    }

    #[NoDiscard]
    public function set(string $key, mixed $value): static
    {
        $this->mutationGuard->assertMutable();

        if (str_contains(haystack: $key, needle: '.')) {
            return new self(
                items: new PutValueByPath(items: $this->items)->put(path: $key, value: $value),
            );
        }

        $items = $this->items;
        $items[$key] = $value;

        return new self(items: $items);
    }

    #[NoDiscard]
    public function forget(string $key): static
    {
        $this->mutationGuard->assertMutable();

        if (! array_key_exists(key: $key, array: $this->items) && str_contains(haystack: $key, needle: '.')) {
            $items = $this->items;
            new DotPath(path: $key)->unsetValue(items: $items);

            return new self(items: $items);
        }

        $items = $this->items;
        unset($items[$key]);

        return new self(items: $items);
    }

    #[NoDiscard]
    public function add(mixed $value): static
    {
        $this->mutationGuard->assertMutable();

        return new self(
            items: new AppendValue(items: $this->items)->append(value: $value),
        );
    }

    #[NoDiscard]
    public function merge(array $items): static
    {
        return new self(items: array_merge($this->items, $items));
    }

    public function count(): int
    {
        return count(value: $this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function isNotEmpty() : bool
    {
        return $this->items !== [];
    }

    public function first(mixed $default = null): mixed
    {
        if ($this->items === []) {
            return $default;
        }

        $copy = $this->items;

        return reset(array: $copy);
    }

    public function last(mixed $default = null): mixed
    {
        if ($this->items === []) {
            return $default;
        }

        $copy = $this->items;

        return end(array: $copy);
    }

    /**
     * @return list<string|int>
     */
    public function keys() : array
    {
        return array_keys(array: $this->items);
    }

    public function values() : static
    {
        return new self(items: array_values(array: $this->items));
    }

    /**
     * @param list<string|int> $keys
     */
    public function only(array $keys) : static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $_, mixed $key) : bool => in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH,
        );

        return new self(items: $filtered);
    }

    /**
     * @param list<string|int> $keys
     */
    public function except(array $keys) : static
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: static fn (mixed $_, mixed $key) : bool => ! in_array(needle: $key, haystack: $keys, strict: true),
            mode    : ARRAY_FILTER_USE_BOTH,
        );

        return new self(items: $filtered);
    }

    /**
     * @return list<mixed>
     */
    public function pluck(string $key) : array
    {
        $plucked = [];

        foreach ($this->items as $item) {
            $plucked[] = is_array(value: $item)
                ? ($item[$key] ?? null)
                : null;
        }

        return $plucked;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function toJson(int $flags = 0) : string
    {
        return new ConvertCollectionToJson(items: $this->items)->toJson(flags: $flags);
    }

    public function isLocked(): bool
    {
        return $this->mutationGuard->isLocked();
    }

    public function lock(): static
    {
        $clone = new self(items: $this->items);
        $clone->mutationGuard->lock();

        return $clone;
    }
}
