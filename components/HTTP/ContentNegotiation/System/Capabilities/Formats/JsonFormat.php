<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats;

final class JsonFormat implements ContentFormatterInterface
{
    public function format(mixed $data) : string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS);
    }

    public function mimeType() : string
    {
        return 'application/json';
    }
}
