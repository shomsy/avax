<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues;

use ArrayIterator;
use IteratorAggregate;
use Override;
use Stringable;

/**
 * @implements IteratorAggregate<string, CacheTag>
 */
final readonly class CacheTags implements Stringable, IteratorAggregate
{
    /** @var array<string, CacheTag> */
    private array $tags;

    public function __construct(
        CacheTag ...$cacheTag,
    ) {
        $tagStrings = array_map(static fn (CacheTag $cacheTag): string => $cacheTag->toString(), $cacheTag);
        $this->tags = array_combine($tagStrings, $cacheTag) ?: [];
    }

    public static function create(string ...$tagNames): self
    {
        $tags = array_map(static fn (string $name): CacheTag => CacheTag::create(name: $name), $tagNames);

        return new self(...$tags);
    }

    public static function empty(): self
    {
        return new self();
    }

    public function add(CacheTag $cacheTag): self
    {
        $newTags                        = $this->tags;
        $newTags[$cacheTag->toString()] = $cacheTag;

        return new self(...$newTags);
    }

    public function remove(CacheTag $cacheTag): self
    {
        $newTags = $this->tags;
        unset($newTags[$cacheTag->toString()]);

        return new self(...$newTags);
    }

    public function hasAny(self $other): bool
    {
        return array_any($this->tags, fn (CacheTag $cacheTag) : bool => $other->has(cacheTag: $cacheTag));
    }

    public function has(CacheTag $cacheTag): bool
    {
        return isset($this->tags[$cacheTag->toString()]);
    }

    public function hasAll(self $other): bool
    {
        return array_all($other->tags, fn (CacheTag $cacheTag) : bool => $this->has(cacheTag: $cacheTag));
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function count(): int
    {
        return count($this->tags);
    }

    #[Override]
    /**
     * @return ArrayIterator<string, CacheTag>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator(array: $this->tags);
    }

    #[Override]
    public function __toString(): string
    {
        return implode(', ', $this->toArray());
    }

    /**
     * @return list<string>
     */
    public function toArray(): array
    {
        return array_keys($this->tags);
    }
}
