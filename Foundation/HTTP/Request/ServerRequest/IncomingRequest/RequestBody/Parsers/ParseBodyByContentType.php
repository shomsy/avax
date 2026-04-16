<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers;

/**
 * Action Owner: Routes body content-type to the appropriate parser.
 */
final readonly class ParseBodyByContentType
{
    /**
     * @param string $contentType
     * @param string $content
     *
     * @return array|null
     */
    public function execute(string $contentType, string $content) : array|null
    {
        $mediaType = strtolower(trim(explode(';', $contentType)[0]));

        return match ($mediaType) {
            'application/json'                  => (new ParseJsonBody())->execute(content: $content),
            'application/x-www-form-urlencoded' => (new ParseFormBody())->execute(content: $content),
            // Note: multipart/form-data is usually handled by PHP into $_FILES and $_POST
            // so we don't necessarily parse it manually from the stream here unless needed.
            default                             => null
        };
    }
}
