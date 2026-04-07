<?php

declare(strict_types=1);

namespace Avax\Container;

/**
 * Stable public contract for contextual binding configuration.
 */
interface ContextBuilderInterface
{
    public function needs(string $abstract) : self;

    public function give(mixed $concrete) : void;
}
