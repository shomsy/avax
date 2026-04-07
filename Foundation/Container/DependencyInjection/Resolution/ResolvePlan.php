<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Resolution;

final readonly class ResolvePlan
{
    /** @param list<string> $steps */
    public function __construct(
        public array $steps = ['lookup', 'build', 'inject', 'extend', 'store']
    ) {}
}
