<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\System\Capabilities\Formats;

interface ContentFormatterInterface
{
    public function format(mixed $data) : string;

    public function mimeType() : string;
}
