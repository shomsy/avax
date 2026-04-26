<?php

declare(strict_types=1);

namespace components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers;

/**
 * ParseBodyByContentType - Routes body content-type to the appropriate parser.
 */
final readonly class ParseBodyByContentType
{
    public function __construct(
        private ParseJsonBody $jsonParser,
        private ParseFormBody $formParser,
    ) {}

    public function execute(string $contentType, string $content) : array|null
    {
        if ($content === '') {
            return null;
        }

        $mediaType = strtolower(string: trim(string: explode(separator: ';', string: $contentType)[0]));

        return match ($mediaType) {
            'application/json'                  => $this->jsonParser->execute(content: $content),
            'application/x-www-form-urlencoded' => $this->formParser->execute(content: $content),
            default                             => null,
        };
    }
}
