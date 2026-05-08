<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\LazySequence;

use Avax\Components\DataStack\Data\System\Capabilities\Sequence\Sequence;
use Closure;
use IteratorAggregate;
use Override;
use Traversable;

/**
 * Lazily transformed iterable pipeline.
 */
final readonly class LazySequence implements IteratorAggregate
{
    /**
     * @param  Closure() : iterable<mixed>  $factory
     * @param  array<int, callable(mixed) : mixed>  $maps
     * @param  array<int, callable(mixed) : bool>  $filters
     */
    private function __construct(
        private Closure $factory,
        private array $maps = [],
        private array $filters = [],
        private ?int $limit = null,
    ) {
    }

    public static function from(iterable $items): self
    {
        return new self(factory: static fn (): iterable => $items);
    }

    /**
     * @param  Closure() : iterable<mixed>  $factory
     */
    public static function fromFactory(Closure $factory): self
    {
        return new self(factory: $factory);
    }

    public function map(callable $callback): self
    {
        $maps = $this->maps;
        $maps[] = $callback;

        return new self(factory: $this->factory, maps: $maps, filters: $this->filters, limit: $this->limit);
    }

    public function filter(callable $callback): self
    {
        $filters = $this->filters;
        $filters[] = $callback;

        return new self(factory: $this->factory, maps: $this->maps, filters: $filters, limit: $this->limit);
    }

    public function take(int $limit): self
    {
        return new self(factory: $this->factory, maps: $this->maps, filters: $this->filters, limit: $limit);
    }

    public function toSequence(): Sequence
    {
        return new Sequence(items: $this->toArray());
    }

    /**
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return iterator_to_array($this->getIterator(), false);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        $count = 0;

        foreach (($this->factory)() as $item) {
            $keep = array_all($this->filters, fn ($filter) => $filter($item));
            if (! $keep) {
                continue;
            }

            $value = $item;

            foreach ($this->maps as $map) {
                $value = $map($value);
            }

            yield $value;

            $count++;

            if ($this->limit !== null && $count >= $this->limit) {
                break;
            }
        }
    }
}
