<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Capabilities\Declaration\Bindings;

use LogicException;

/**
 * Register a target-specific dependency override.
 */
final class RegisterForTarget
{
    private string                   $needs = '';
    private readonly string          $consumer;
    private readonly ServiceRegistry $registry;

    public function __construct(
        ServiceRegistry $registry,
        string          $consumer
    )
    {
        $this->registry = $registry;
        $this->consumer = $consumer;
    }

    public function needs(string $abstract) : self
    {
        $this->needs = $abstract;

        return $this;
    }

    public function give(mixed $implementation) : void
    {
        if ($this->needs === '') {
            throw new LogicException(
                message: 'Call needs() before give() when registering a target-specific dependency.'
            );
        }

        $this->registry->addContextual(
            consumer: $this->consumer,
            needs   : $this->needs,
            give    : $implementation
        );
    }
}
