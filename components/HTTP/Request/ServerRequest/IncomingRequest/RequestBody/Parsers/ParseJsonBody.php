<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers;

use JsonException;

/**
 * Action Owner: Parses JSON request body.
 */
final readonly class ParseJsonBody
{
    public function execute(string $content) : array|null
    {
        $content = trim(string: $content);
        if ($content === '') {
            return null;
        }

        try {
            $data = json_decode(json: $content, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);

            return (is_array(value: $data) || is_object(value: $data)) ? $data : null;
        } catch (JsonException) {
            return null;
        }
    }
}
