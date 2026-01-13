<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Testing;

use Avax\HTTP\Router\RouterRuntimeInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

/**
 * Fault injection testing framework for router resilience.
 *
 * Enables chaos testing to ensure router reliability under failure conditions:
 * - Cache corruption scenarios
 * - Filesystem failures
 * - Memory exhaustion
 * - Concurrent access issues
 * - Network partition simulation
 */
final class FaultInjection
{
    private array $activeFaults = [];

    /**
     * Injects filesystem read failure for cache files.
     */
    public function injectFilesystemReadFailure(string $pattern = '*.php') : void
    {
        $this->activeFaults['filesystem_read'] = [
            'type'    => 'filesystem_read_failure',
            'pattern' => $pattern,
            'handler' => function (string $path) {
                if (fnmatch($this->activeFaults['filesystem_read']['pattern'], basename($path))) {
                    throw new RuntimeException(message: "Injected filesystem read failure for: {$path}");
                }
            }
        ];
    }

    /**
     * Injects filesystem write failure for cache files.
     */
    public function injectFilesystemWriteFailure(string $pattern = '*.php') : void
    {
        $this->activeFaults['filesystem_write'] = [
            'type'    => 'filesystem_write_failure',
            'pattern' => $pattern,
            'handler' => function (string $path) {
                if (fnmatch($this->activeFaults['filesystem_write']['pattern'], basename($path))) {
                    throw new RuntimeException(message: "Injected filesystem write failure for: {$path}");
                }
            }
        ];
    }

    /**
     * Injects memory exhaustion during route processing.
     */
    public function injectMemoryExhaustion(int $triggerAfterRoutes = 5) : void
    {
        $processedRoutes                         = 0;
        $this->activeFaults['memory_exhaustion'] = [
            'type'          => 'memory_exhaustion',
            'trigger_after' => $triggerAfterRoutes,
            'handler'       => function () use (&$processedRoutes, $triggerAfterRoutes) {
                $processedRoutes++;
                if ($processedRoutes >= $triggerAfterRoutes) {
                    throw new RuntimeException(message: "Injected memory exhaustion after {$processedRoutes} routes");
                }
            }
        ];
    }

    /**
     * Injects random route resolution failures.
     */
    public function injectRandomResolutionFailures(float $failureRate = 0.1) : void
    {
        $this->activeFaults['random_failure'] = [
            'type'    => 'random_resolution_failure',
            'rate'    => $failureRate,
            'handler' => function () use ($failureRate) {
                if (mt_rand(0, 100) / 100 < $failureRate) {
                    throw new RuntimeException(message: "Injected random resolution failure");
                }
            }
        ];
    }

    /**
     * Injects cache corruption by modifying loaded data.
     */
    public function injectCacheCorruption() : void
    {
        $this->activeFaults['cache_corruption'] = [
            'type'    => 'cache_corruption',
            'handler' => function (&$data) {
                if (is_array($data)) {
                    // Corrupt route data randomly
                    $data = array_slice($data, 0, mt_rand(0, count($data) - 1));
                }
            }
        ];
    }

    /**
     * Injects network partition by simulating slow/unavailable operations.
     */
    public function injectNetworkPartition(int $delayMs = 5000) : void
    {
        $this->activeFaults['network_partition'] = [
            'type'     => 'network_partition',
            'delay_ms' => $delayMs,
            'handler'  => function () use ($delayMs) {
                usleep($delayMs * 1000); // Convert to microseconds
                throw new RuntimeException(message: "Injected network partition delay");
            }
        ];
    }

    /**
     * Tests router resilience under injected faults.
     *
     * @return array{tests_run: int, failures: int, results: array}
     */
    public function testRouterResilience(RouterRuntimeInterface $router, array $testRequests) : array
    {
        $results  = [];
        $failures = 0;
        $testsRun = 0;

        foreach ($this->activeFaults as $faultName => $faultConfig) {
            $results[$faultName] = [
                'fault_type' => $faultConfig['type'],
                'tests'      => [],
                'passed'     => 0,
                'failed'     => 0,
            ];

            foreach ($testRequests as $request) {
                $testsRun++;
                try {
                    $response                       = $router->resolve(request: $request);
                    $results[$faultName]['tests'][] = [
                        'request'         => $this->summarizeRequest(request: $request),
                        'result'          => 'passed',
                        'response_status' => $response->getStatusCode(),
                    ];
                    $results[$faultName]['passed']++;
                } catch (Throwable $e) {
                    $failures++;
                    $results[$faultName]['tests'][] = [
                        'request' => $this->summarizeRequest(request: $request),
                        'result'  => 'failed',
                        'error'   => $e->getMessage(),
                    ];
                    $results[$faultName]['failed']++;
                }
            }
        }

        return [
            'tests_run' => $testsRun,
            'failures'  => $failures,
            'results'   => $results,
        ];
    }

    /**
     * Creates a summary of a request for test reporting.
     */
    private function summarizeRequest(ServerRequestInterface $request) : string
    {
        return sprintf(
            '%s %s',
            $request->getMethod(),
            $request->getUri()->getPath()
        );
    }

    /**
     * Clears all active fault injections.
     */
    public function clearAllFaults() : void
    {
        $this->activeFaults = [];
    }

    /**
     * Gets currently active faults.
     */
    public function getActiveFaults() : array
    {
        return array_keys($this->activeFaults);
    }

    /**
     * Triggers an injected fault if active.
     */
    public function triggerFault(string $faultType, mixed ...$args) : void
    {
        if ($this->shouldInjectFault(faultType: $faultType)) {
            $handler = $this->activeFaults[$faultType]['handler'];
            $handler(...$args);
        }
    }

    /**
     * Checks if router should handle faults gracefully.
     *
     * Call this in router components to trigger injected faults.
     */
    public function shouldInjectFault(string $faultType) : bool
    {
        return isset($this->activeFaults[$faultType]);
    }
}