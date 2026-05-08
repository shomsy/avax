<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Foundation;

final readonly class TaskId
{
    public function __construct(
        public string $value,
    ) {}

    public static function generate() : self
    {
        return new self(bin2hex(random_bytes(8)));
    }

    public function toString() : string
    {
        return $this->value;
    }

    public function equals(TaskId $other) : bool
    {
        return $this->value === $other->value;
    }
}
