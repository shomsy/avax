<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Coercion\DtoSystem;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use Override;
use Traversable;

/**
 * Collection of validation violations for DTO
 *
 * @implements IteratorAggregate<int, DataTransferViolation>
 */
final readonly class DataTransferViolations implements Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @param  DataTransferViolation[]  $violations
     */
    public function __construct(
        private array $violations = [],
    ) {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @param  list<DataTransferViolation>  $violations
     */
    public static function from(array $violations): self
    {
        return new self($violations);
    }

    public function add(DataTransferViolation $dataTransferViolation): self
    {
        return new self([...$this->violations, $dataTransferViolation]);
    }

    public function isEmpty(): bool
    {
        return $this->violations === [];
    }

    #[Override]
    public function count(): int
    {
        return count($this->violations);
    }

    /**
     * @return Traversable<int, DataTransferViolation>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        yield from $this->violations;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return array_map(
            static fn (DataTransferViolation $dataTransferViolation): array => $dataTransferViolation->jsonSerialize(),
            $this->violations,
        );
    }
}
