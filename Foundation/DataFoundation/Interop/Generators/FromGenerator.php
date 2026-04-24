<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Interop\Generators;

use Avax\DataFoundation\Flows\LazySequence\LazySequence;
use Closure;

/**
 * Generator entry point into lazy flows.
 */
final readonly class FromGenerator
{
    /**
     * @param Closure(): iterable<mixed> $factory
     */
    public static function toLazySequence(Closure $factory) : LazySequence
    {
        return LazySequence::fromFactory(factory: $factory);
    }
}
