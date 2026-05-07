<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\Capabilities\Parts;

use Stringable;

final readonly class Query implements Stringable
{
    private string $query;

    /**
     * @param string|array<string, mixed> $query
     */
    public function __construct(string|array $query)
    {
        if (is_array($query)) {
            $this->query = http_build_query($query);
        } else {
            $this->query = ltrim($query, '?');
        }
    }

    public function __toString() : string
    {
        return $this->query;
    }
}
