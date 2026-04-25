<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\IdentifyCachedValues;

use ArrayIterator;
use Stringable;
use Traversable;

final readonly class CacheTags implements Stringable
{
    /** @var array<string, CacheTag> */
    private array $tags;

    public function __construct(
        CacheTag ...$tags
    )
    {
        $tagStrings = array_map(fn (CacheTag $tag) => $tag->toString(), $tags);
        $this->tags = array_combine($tagStrings, $tags);
    }

    public static function create(string ...$tagNames) : self
    {
        $tags = array_map(fn (string $name) => CacheTag::create($name), $tagNames);

        return new self(...$tags);
    }

    public static function empty() : self
    {
        return new self();
    }

    public function add(CacheTag $tag) : self
    {
        $newTags                   = $this->tags;
        $newTags[$tag->toString()] = $tag;

        return new self(...$newTags);
    }

    public function remove(CacheTag $tag) : self
    {
        $newTags = $this->tags;
        unset($newTags[$tag->toString()]);

        return new self(...$newTags);
    }

    public function hasAny(self $other) : bool
    {
        foreach ($this->tags as $tag) {
            if ($other->has($tag)) {
                return true;
            }
        }

        return false;
    }

    public function has(CacheTag $tag) : bool
    {
        return isset($this->tags[$tag->toString()]);
    }

    public function hasAll(self $other) : bool
    {
        foreach ($other->tags as $tag) {
            if (! $this->has($tag)) {
                return false;
            }
        }

        return true;
    }

    public function isEmpty() : bool
    {
        return $this->count() === 0;
    }

    public function count() : int
    {
        return count($this->tags);
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->tags);
    }

    public function __toString() : string
    {
        return implode(', ', $this->toArray());
    }

    public function toArray() : array
    {
        return array_keys($this->tags);
    }
}