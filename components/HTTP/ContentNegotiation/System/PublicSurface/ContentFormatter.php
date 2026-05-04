<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\PublicSurface;

use Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Negotiator\AcceptHeaderParser;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

interface ContentFormatter
{
    public function format(mixed $data): string;

    public function mimeType(): string;
}
