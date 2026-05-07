<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\Flows\NegotiateApiVersion;

final readonly class NegotiateApiVersion
{
    /**
     * @param list<string> $available
     */
    public function negotiate(string $requested, array $available, string $default) : string
    {
        if (in_array($requested, $available, true)) {
            return $requested;
        }

        return $default;
    }
}
