<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage;

/**
 * LinkedNodeStorage — immutable singly-linked node for chain-based structures.
 *
 * @template T
 */
final readonly class LinkedNodeStorage
{
    /**
     * @param T            $value
     * @param self<T>|null $next
     */
    public function __construct(
        public mixed     $value,
        public self|null $next = null,
    ) {}

    /**
     * Prepend a new node before this one.
     *
     * @param T $value
     *
     * @return self<T>
     */
    public function prepend(mixed $value) : self
    {
        return new self(value: $value, next: $this);
    }

    /**
     * Append a new node after this one (creates a new chain).
     *
     * @param T $value
     *
     * @return self<T>
     */
    public function append(mixed $value) : self
    {
        if ($this->next === null) {
            return new self(value: $this->value, next: new self(value: $value));
        }

        return new self(value: $this->value, next: $this->next->append(value: $value));
    }

    /**
     * @return list<mixed>
     */
    public function toArray() : array
    {
        $result  = [$this->value];
        $current = $this->next;
        while ( $current !== null ) {
            $result[] = $current->value;
            $current  = $current->next;
        }

        return $result;
    }

    public function length() : int
    {
        $count   = 1;
        $current = $this->next;
        while ( $current !== null ) {
            $count++;
            $current = $current->next;
        }

        return $count;
    }
}
