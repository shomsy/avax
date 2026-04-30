<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Headers;

final class HeaderValue
{
    private array $values;

    public function __construct(string|array $values)
    {
        $this->values = is_array($values) ? array_values($values) : [$values];
    }

    public function all(): array
    {
        return $this->values;
    }

    public function line(): string
    {
        return implode(', ', $this->values);
    }

    public function first(): string
    {
        return $this->values[0] ?? '';
    }
}
