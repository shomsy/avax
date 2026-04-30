<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Configuration;

use Avax\Components\HTTP\Request\System\PublicSurface\Request;

final class RequestBuilder
{
    public function build() : Request
    {
        return new Request(...); // Placeholder
    }
}
