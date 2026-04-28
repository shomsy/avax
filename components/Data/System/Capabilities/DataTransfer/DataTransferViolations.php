<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\DataTransfer;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * Collection of validation violations for DTO
 * 
 * @implements IteratorAggregate<int, DataTransferViolation>
 */
final readonly class DataTransferViolations implements Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @param DataTransferViolation[] $violations
     */
    public function __construct(
        private array $violations = [],
    ) {}

    public static function empty(): self
    {
        return new self([]);
    }

    public static function from(array $violations): self
    {
        return new self($violations);
    }

    public function add(DataTransferViolation $violation): self
    {
        return new self([...$this->violations, $violation]);
    }

    public function isEmpty(): bool
    {
        return empty($this->violations);
    }

    public function count(): int
    {
        return count($this->violations);
    }

    /**
     * @return Traversable<int, DataTransferViolation>
     */
    public function getIterator(): Traversable
    {
        yield from $this->violations;
    }

    public function jsonSerialize(): array
    {
        return array_map(
            static fn (DataTransferViolation $v) => $v->jsonSerialize(),
            $this->violations
        );
    }
}