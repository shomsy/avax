<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\PublicSurface;

final readonly class NegotiatedContent
{
    public function __construct(
        public string           $mimeType,
        public ContentFormatter $formatter,
    ) {}
}
