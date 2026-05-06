<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings;

use LogicException;

/**
 * Register a target-specific dependency override.
 */
final class RegisterForTarget
{
    private string $needs = '';

    public function __construct(private readonly DependencyRegistry $dependencyRegistry, private readonly string $consumer)
    {
    }

    public function needs(string $abstract): self
    {
        $this->needs = $abstract;

        return $this;
    }

    public function give(mixed $implementation): void
    {
        if ($this->needs === '') {
            throw new LogicException(
                message: 'Call needs() before give() when registering a target-specific dependency.',
            );
        }

        $this->dependencyRegistry->addContextual(
            consumer: $this->consumer,
            needs   : $this->needs,
            give    : $implementation,
        );
    }
}
