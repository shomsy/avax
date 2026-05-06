<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Body;

final readonly class ParseFormBody
{
    /**
     * @return array<string, mixed>
     */
    public function parse(string $body): array
    {
        if ($body === '') {
            return [];
        }

        parse_str(string: $body, result: $parsed);

        return $parsed;
    }
}
