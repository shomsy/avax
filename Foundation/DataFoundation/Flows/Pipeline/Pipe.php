<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Flows\Pipeline;

use Closure;

/**
 * One named pipeline stage.
 */
final readonly class Pipe
{
    public function __construct(
        private Closure     $callback,
        private string|null $name = null,
    ) {}

    public static function from(callable $callback, string|null $name = null) : self
    {
        return new self(callback: $callback(...), name: $name);
    }

    public function name() : string
    {
        return $this->name ?? 'pipe';
    }

    public function __invoke(mixed $value) : mixed
    {
        return ($this->callback)($value);
    }
}
