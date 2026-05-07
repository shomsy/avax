<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\Flows\NegotiateContentType;

final readonly class NegotiateContentType
{
    /**
     * @param list<string> $available
     */
    public function negotiate(string $acceptHeader, array $available, string $default = 'application/json') : string
    {
        foreach ($available as $type) {
            if (str_contains($acceptHeader, $type) || str_contains($acceptHeader, '*/*')) {
                return $type;
            }
        }

        return $default;
    }
}
