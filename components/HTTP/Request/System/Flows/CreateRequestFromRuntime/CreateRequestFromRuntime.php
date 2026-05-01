<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Flows\CreateRequestFromRuntime;

use Avax\Components\HTTP\Request\System\PublicSurface\Request;

final class CreateRequestFromRuntime
{
    public function execute(array $headers, string $body): Request
    {
        // Implementation for runtime-driven requests (e.g. from Swoole or RoadRunner)
        return new Request(...); // Simplified
    }
}
