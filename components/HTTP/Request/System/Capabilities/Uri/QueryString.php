<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Uri;

final readonly class QueryString
{
    public function __construct(
        private string $query,
    ) {
    }

    public function toArray(): array
    {
        $params = [];
        parse_str($this->query, $params);

        return $params;
    }

    public function toString(): string
    {
        return $this->query;
    }
}
