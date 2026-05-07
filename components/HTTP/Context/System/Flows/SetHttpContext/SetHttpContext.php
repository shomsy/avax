<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Context\System\Flows\SetHttpContext;

final readonly class SetHttpContext
{
    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function set(string $key, mixed $value, array $context) : array
    {
        $context[$key] = $value;

        return $context;
    }
}
