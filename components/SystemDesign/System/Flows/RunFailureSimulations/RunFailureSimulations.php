<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\RunFailureSimulations;

use Avax\Components\SystemDesign\System\Capabilities\Capacity\CapacityModel;
use Avax\Components\SystemDesign\System\Capabilities\FailureSimulation\FailureSimulation;

/**
 * Runs failure simulations against a capacity model.
 *
 * Each simulation applies a failure mode and detects whether the
 * system design catches the resulting violation.
 *
 * @experimental V3 labs
 */
final class RunFailureSimulations
{
    /**
     * @var list<array{
     *     name: string,
     *     failure_mode: string,
     *     description: string,
     *     parameters: array<int|string, mixed>,
     * }>
     */
    private static array $defaultFailures
        = [
            [
                'name'         => 'Cache Complete Outage',
                'failure_mode' => 'cache_outage',
                'description'  => 'Cache layer becomes completely unavailable.',
                'parameters'   => [],
            ],
            [
                'name'         => 'Queue Flood',
                'failure_mode' => 'queue_flood',
                'description'  => 'Queue depth grows beyond consumer capacity.',
                'parameters'   => [],
            ],
            [
                'name'         => 'Database Slow',
                'failure_mode' => 'database_slow',
                'description'  => 'Database response times spike dramatically.',
                'parameters'   => [],
            ],
            [
                'name'         => 'Traffic Overload',
                'failure_mode' => 'traffic_overload',
                'description'  => 'Traffic exceeds peak capacity estimate.',
                'parameters'   => [],
            ],
            [
                'name'         => 'Replication Lag Spike',
                'failure_mode' => 'replication_lag_spike',
                'description'  => 'Replica lag spikes to multiple seconds.',
                'parameters'   => [],
            ],
            [
                'name'         => 'SLO Budget Exhausted',
                'failure_mode' => 'slo_budget_exhausted',
                'description'  => 'System consumes its entire SLO error budget.',
                'parameters'   => [],
            ],
        ];

    /**
     * Run all failure simulations against a capacity model.
     *
     * @param list<array{name: string, failure_mode: string, description: string, parameters: array<int|string,
     *                                 mixed>}>|null $customFailures
     *
     * @return array{
     *     total: int,
     *     violations_detected: int,
     *     clean: int,
     *     simulations: list<array{
     *         simulation: string,
     *         failure_mode: string,
     *         violation_detected: bool,
     *         violation: ?array{
     *             type: string,
     *             severity: string,
     *             description: string,
     *             impact: string,
     *             mitigation: string,
     *         },
     *     }>,
     * }
     */
    public function execute(CapacityModel $model, ?array $customFailures = null) : array
    {
        $failures   = $customFailures ?? self::$defaultFailures;
        $results    = [];
        $violations = 0;
        $clean      = 0;

        foreach ($failures as $failureConfig) {
            $sim       = FailureSimulation::fromConfig($failureConfig);
            $result    = $sim->run($model);
            $results[] = $result;

            if ($result['violation_detected']) {
                $violations++;
            } else {
                $clean++;
            }
        }

        return [
            'total'               => count($failures),
            'violations_detected' => $violations,
            'clean'               => $clean,
            'simulations'         => $results,
        ];
    }
}
