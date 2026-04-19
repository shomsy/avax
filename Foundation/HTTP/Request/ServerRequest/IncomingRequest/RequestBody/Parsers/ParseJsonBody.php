<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers;

use JsonException;

/**
 * Action Owner: Parses JSON request body.
 */
final readonly class ParseJsonBody
{
    public function execute(string $content) : array|null
    {
        $content = trim($content);
        if ($content === '') {
            return null;
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            return (is_array($data) || is_object($data)) ? $data : null;
        } catch (JsonException) {
            return null;
        }
    }
}
