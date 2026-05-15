<?php

final readonly class MissingClassDocblock
{
    public function __construct(
        private string $name,
    ) {}

    public function getName(): string
    {
        return trim($this->name);
    }
}
