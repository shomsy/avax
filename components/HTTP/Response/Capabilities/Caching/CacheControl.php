<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Caching;

final readonly class CacheControl
{
    /**
     * @param array<string, int|string|true> $directives
     */
    public function __construct(
        public array $directives = [],
    ) {}
}
