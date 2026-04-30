<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Capabilities\Streaming;

use Avax\Components\HTTP\Response\System\PublicSurface\Response;

final class StreamingResponse
{
    public function create(callable $callback) : Response
    {
        return new Response(); // Placeholder
    }
}
