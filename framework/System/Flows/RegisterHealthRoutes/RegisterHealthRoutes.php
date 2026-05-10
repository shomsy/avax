<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\RegisterHealthRoutes;

use Avax\Framework\System\Capabilities\Health\CheckLiveness;
use Avax\Framework\System\Capabilities\Health\CheckReadiness;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Avax\Framework\System\PublicSurface\App;

/**
 * RegisterHealthRoutes — wires health endpoints into the App API.
 *
 * Registers:
 * - GET /health — general health
 * - GET /health/live — process liveness
 * - GET /health/ready — dependency readiness
 */
final readonly class RegisterHealthRoutes
{
    public function __construct(
        private CheckLiveness $liveness,
        private CheckReadiness $readiness,
        private bool $productionMode = false,
    ) {
    }

    public function register(App $app): App
    {
        $app->get('/health', $this->healthHandler());
        $app->get('/health/live', $this->livenessHandler());
        $app->get('/health/ready', $this->readinessHandler());

        return $app;
    }

    private function healthHandler(): \Closure
    {
        return fn () => [
            'status' => 'ok',
            'timestamp' => date('c'),
        ];
    }

    private function livenessHandler(): \Closure
    {
        return fn () => $this->handleLiveness();
    }

    private function readinessHandler(): \Closure
    {
        return fn () => $this->handleReadiness();
    }

    /**
     * @return array<string, mixed>
     */
    private function handleLiveness(): array
    {
        $report = $this->liveness->check();

        $response = [
            'status' => $report->overall->value,
        ];

        if (!$this->productionMode) {
            $response['findings'] = array_map(
                static fn ($f) => [
                    'check' => $f->check,
                    'status' => $f->status->value,
                    'message' => $f->message,
                ],
                $report->findings,
            );
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function handleReadiness(): array
    {
        $report = $this->readiness->check();

        $response = [
            'status' => $report->status->value,
        ];

        if (!$this->productionMode) {
            $response['checks'] = array_map(
                static fn ($f) => [
                    'check' => $f->check,
                    'status' => $f->status->value,
                    'message' => $f->message,
                ],
                $report->checks,
            );
        }

        if ($report->version !== '') {
            $response['version'] = $report->version;
        }

        return $response;
    }
}
