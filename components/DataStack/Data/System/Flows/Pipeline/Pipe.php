<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Pipeline;

use Closure;

/**
 * One named pipeline stage.
 */
final readonly class Pipe
{
    public function __construct(
        private Closure $callback,
        private ?string $name = null,
    ) {
    }

    public static function from(callable $callback, ?string $name = null): self
    {
        return new self(callback: $callback(...), name: $name);
    }

    public function name(): string
    {
        return $this->name ?? 'pipe';
    }

    public function __invoke(mixed $value): mixed
    {
        return ($this->callback)($value);
    }
}
