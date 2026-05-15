<?php

/**
 * Small class within thresholds.
 */
final readonly class SmallClass
{
    public function __construct(
        private string $name,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }
}
