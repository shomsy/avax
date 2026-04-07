<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Policies\Decisions;

/**
 * Non-error decision that permits resolution.
 */
final readonly class ResolutionAllowed
{
    public function __construct(
        public mixed  $data = null,
        public string $message = 'Resolution allowed.'
    ) {}
}
