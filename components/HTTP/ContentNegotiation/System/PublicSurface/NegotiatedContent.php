<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\PublicSurface;

use Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Negotiator\AcceptHeaderParser;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

final readonly class NegotiatedContent
{
    public function __construct(
        public string           $mimeType,
        public ContentFormatter $formatter,
    )
    {
    }
}
