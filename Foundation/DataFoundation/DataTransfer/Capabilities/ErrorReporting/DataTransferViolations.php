<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\ErrorReporting;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @implements IteratorAggregate<int, DataTransferViolation>
 */
final readonly class DataTransferViolations implements Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @param DataTransferViolation[] $violations
     */
    private function __construct(private array $violations) {}

    public static function empty() : self
    {
        return new self(violations: []);
    }

    /**
     * @param DataTransferViolation[] $violations
     */
    public static function from(array $violations) : self
    {
        return new self(violations: array_values(array: $violations));
    }

    public function add(DataTransferViolation $violation) : self
    {
        return new self(violations: [...$this->violations, $violation]);
    }

    public function merge(self $violations) : self
    {
        return new self(violations: [...$this->violations, ...$violations->all()]);
    }

    /**
     * @return DataTransferViolation[]
     */
    public function all() : array
    {
        return $this->violations;
    }

    public function isEmpty() : bool
    {
        return $this->violations === [];
    }

    public function count() : int
    {
        return count(value: $this->violations);
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator(array: $this->violations);
    }

    public function byPath() : array
    {
        $grouped = [];

        foreach ($this->violations as $violation) {
            $grouped[$violation->path]   ??= [];
            $grouped[$violation->path][] = $violation->message;
        }

        return $grouped;
    }

    public function toLegacyErrors() : array
    {
        $errors = [];

        foreach ($this->violations as $violation) {
            $errors[$violation->path] = $violation->message;
        }

        return $errors;
    }

    public function jsonSerialize() : array
    {
        return array_map(
            callback: static fn (DataTransferViolation $violation) : array => $violation->jsonSerialize(),
            array   : $this->violations,
        );
    }
}
