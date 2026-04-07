<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Definitions\Contracts;

/**
 * Fluent write-side configuration for contextual bindings.
 */
interface ContextBuilderInterface
{
    public function needs(string $abstract) : self;

    public function give(mixed $concrete) : void;
}
