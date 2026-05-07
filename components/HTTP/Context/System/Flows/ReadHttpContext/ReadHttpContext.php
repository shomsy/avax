<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Context\System\Flows\ReadHttpContext;

final readonly class ReadHttpContext
{
    /**
     * @param array<string, mixed> $context
     */
    public function read(string $key, array $context, mixed $default = null) : mixed
    {
        return $context[$key] ?? $default;
    }
}
