<?php

declare(strict_types=1);

namespace Avax\Examples\GoldenPathRuntimeApp\Domain\Job;

use Avax\Components\Operations\Queue\System\Foundation\JobInterface;
use Avax\Components\Operations\Resilience\System\Capabilities\CircuitBreaker\CircuitBreaker;
use Avax\Components\Operations\Resilience\System\Capabilities\Timeout\Timeout;
use RuntimeException;

/**
 * Processes a webhook payload with resilience patterns.
 *
 * Wraps the external service call in a circuit breaker and timeout
 * to protect against slow or failing downstream dependencies.
 */
final class ProcessWebhookJob implements JobInterface
{
    public function __construct(
        private readonly CircuitBreaker $circuitBreaker = new CircuitBreaker(),
        private readonly Timeout        $timeout = new Timeout(timeoutMs: 5000),
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function handle(array $data = []) : void
    {
        $this->circuitBreaker->run(function () use ($data) : void {
            $this->timeout->run(function () use ($data) : void {
                $payload = json_decode($data['payload'] ?? '', true);

                if ($payload === null) {
                    throw new RuntimeException('Invalid JSON payload');
                }

                // Simulate processing time
                // In production this would call external APIs, write to DB, etc.
            });
        });
    }
}
