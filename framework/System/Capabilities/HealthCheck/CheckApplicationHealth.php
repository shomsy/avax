<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\HealthCheck;

use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;

/**
 * CheckApplicationHealth — Baseline health endpoint for V4-01.
 *
 * Returns a minimal JSON response indicating framework status.
 * Full health/live/ready endpoints come in later V4 stages.
 */
final readonly class CheckApplicationHealth
{
    public function __construct(
        private ResponseFactory $responseFactory,
    ) {
    }

    public function check(): ResponseInterface
    {
        return $this->responseFactory->json(data: ['status' => 'ok']);
    }
}
