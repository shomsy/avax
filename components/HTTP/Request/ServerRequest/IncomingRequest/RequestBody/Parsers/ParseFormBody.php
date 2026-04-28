<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers;

/**
 * Action Owner: Parses URL-encoded form data (application/x-www-form-urlencoded).
 */
final readonly class ParseFormBody
{
    /**
     * @return array<string, mixed>
     */
    public function execute(string $content) : array
    {
        if ($content === '') {
            return [];
        }

        $data = [];
        parse_str(string: $content, result: $data);

        return $data;
    }
}
