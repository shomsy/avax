<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Batch;

use ArrayIterator;
use Avax\Components\DataStack\Data\Exceptions\InvalidFlowException;
use Avax\Components\DataStack\Data\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Override;
use Traversable;

/**
 * Fixed-size batches over ordered input.
 */
final readonly class Batch implements Countable, IteratorAggregate
{
    /**
     * @param array<int, array<int, mixed>> $batches
     */
    private function __construct(
        private array $batches,
    ) {}

    public static function from(iterable $items, int $size): self
    {
        if ($size <= 0) {
            throw InvalidFlowException::invalidBatchSize(size: $size);
        }

        return new self(
            batches: array_chunk(
                array : array_values(NormalizedIterable::toArrayPreserveKeys(iterable: $items)),
                length: $size,
            ),
        );
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function all(): array
    {
        return $this->batches;
    }

    #[Override]
    public function count(): int
    {
        return count($this->batches);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator(array: $this->batches);
    }
}
