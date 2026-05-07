<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\PublicSurface;

interface ContentFormatter
{
    public function format(mixed $data) : string;

    public function mimeType() : string;
}
