<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Body;

use JsonException;

final readonly class ParseJsonBody
{
    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    public function parse(string $body) : array
    {
        if (trim(string: $body) === '') {
            return [];
        }

        $decoded = json_decode(json: $body, associative: true, flags: JSON_THROW_ON_ERROR);

        return is_array(value: $decoded) ? $decoded : [];
    }
}
