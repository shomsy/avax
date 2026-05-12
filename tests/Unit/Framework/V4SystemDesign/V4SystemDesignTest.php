<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4SystemDesign;

use Avax\Framework\System\Capabilities\SystemDesign\EstimateRuntimeCapacity;
use Avax\Framework\System\Capabilities\SystemDesign\GenerateRuntimeArchitectureReport;
use Avax\Framework\System\Capabilities\SystemDesign\Foundation\ArchitectureFinding;
use Avax\Framework\System\Capabilities\SystemDesign\Foundation\CapacityRecommendation;
use Avax\Framework\System\Capabilities\SystemDesign\Foundation\ConsistencyFinding;
use Avax\Framework\System\Capabilities\SystemDesign\Foundation\FailureSimulationResult;
use Avax\Framework\System\Capabilities\SystemDesign\Foundation\RuntimeArchitectureReport;
use Avax\Framework\System\Capabilities\SystemDesign\InspectOutboxConsistency;
use Avax\Framework\System\Capabilities\SystemDesign\RunFailureSimulation;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class V4SystemDesignTest extends TestCase
{
    #[Test]
    public function architecture_report_generated(): void
    {
        $report = new GenerateRuntimeArchitectureReport([
            static fn () => [new ArchitectureFinding('Router', 'hot_path', '', 'Router is on the hot path.')],
        ]);

        $result = $report->generate();

        self::assertCount(1, $result->findings);
        self::assertSame('Router', $result->findings[0]->component);
    }

    #[Test]
    public function component_muscle_classification(): void
    {
        $finding = new ArchitectureFinding('Cache', 'reusable_capability', '', 'Shared across flows.');

        self::assertSame('Cache', $finding->component);
        self::assertSame('reusable_capability', $finding->classification);
    }

    #[Test]
    public function capacity_recommendation_generated(): void
    {
        $estimator = new EstimateRuntimeCapacity();

        $recommendations = $estimator->recommend([
            'max_workers' => 4,
            'avg_latency_ms' => 50,
            'target_requests_per_second' => 100,
            'db_pool_size' => 10,
            'cache_strategy' => 'memory',
        ]);

        self::assertNotEmpty($recommendations);
        self::assertContainsOnlyInstancesOf(CapacityRecommendation::class, $recommendations);
    }

    #[Test]
    public function queue_worker_recommendation_uses_config(): void
    {
        $estimator = new EstimateRuntimeCapacity();

        $recommendations = $estimator->recommend([
            'avg_latency_ms' => 100,
            'target_requests_per_second' => 200,
        ]);

        $workerRec = array_values(array_filter(
            $recommendations,
            static fn ($r) => $r->area === 'worker_count',
        ))[0] ?? null;

        self::assertNotNull($workerRec);
        // Little's Law: 200 * 100 / 1000 = 20 workers
        self::assertStringContainsString('20', $workerRec->recommendation);
    }

    #[Test]
    public function db_pool_recommendation_uses_config(): void
    {
        $estimator = new EstimateRuntimeCapacity();

        $recommendations = $estimator->recommend(['db_pool_size' => 5]);

        $poolRec = array_values(array_filter(
            $recommendations,
            static fn ($r) => $r->area === 'connection_pool',
        ))[0] ?? null;

        self::assertNotNull($poolRec);
        self::assertStringContainsString('5', $poolRec->recommendation);
    }

    #[Test]
    public function outbox_inspection_works(): void
    {
        $inspector = new InspectOutboxConsistency();

        $findings = $inspector->inspect([
            'pending_count' => 5,
            'failed_count' => 0,
            'relayed_count' => 100,
        ]);

        self::assertNotEmpty($findings);
        self::assertContainsOnlyInstancesOf(ConsistencyFinding::class, $findings);
    }

    #[Test]
    public function outbox_warns_on_high_pending(): void
    {
        $inspector = new InspectOutboxConsistency();

        $findings = $inspector->inspect([
            'pending_count' => 200,
            'failed_count' => 0,
            'relayed_count' => 50,
        ]);

        $pendingFinding = array_values(array_filter(
            $findings,
            static fn ($f) => $f->check === 'outbox_pending',
        ))[0] ?? null;

        self::assertNotNull($pendingFinding);
        self::assertSame('warning', $pendingFinding->status);
    }

    #[Test]
    public function failure_simulation_returns_finding(): void
    {
        $simulator = new RunFailureSimulation();

        $result = $simulator->simulate('queue_failure', ['failed_jobs' => 3]);

        self::assertSame('queue_failure', $result->scenario);
        self::assertSame('simulated', $result->status);
        self::assertNotEmpty($result->findings);
        self::assertNotEmpty($result->recommendations);
    }

    #[Test]
    public function unknown_scenario_returns_unknown(): void
    {
        $simulator = new RunFailureSimulation();

        $result = $simulator->simulate('unknown_scenario');

        self::assertSame('unknown', $result->status);
        self::assertNotEmpty($result->findings);
    }

    #[Test]
    public function simulate_database_failure(): void
    {
        $simulator = new RunFailureSimulation();

        $result = $simulator->simulate('database_failure', ['pool_size' => 20]);

        self::assertSame('database_failure', $result->scenario);
        self::assertStringContainsString('20', implode(' ', $result->findings));
    }

    #[Test]
    public function simulate_memory_pressure(): void
    {
        $simulator = new RunFailureSimulation();

        $result = $simulator->simulate('memory_pressure', ['memory_mb' => 512]);

        self::assertSame('memory_pressure', $result->scenario);
        self::assertStringContainsString('512', implode(' ', $result->findings));
    }

    #[Test]
    public function simulate_message_relay_failure(): void
    {
        $simulator = new RunFailureSimulation();

        $result = $simulator->simulate('message_relay_failure', ['outbox_pending' => 50]);

        self::assertSame('message_relay_failure', $result->scenario);
        self::assertStringContainsString('50', implode(' ', $result->findings));
    }

    #[Test]
    public function architecture_report_combines_all_sections(): void
    {
        $report = new RuntimeArchitectureReport(
            findings: [new ArchitectureFinding('Test', 'test')],
            recommendations: [new CapacityRecommendation('test', 'Test', 'Test reason')],
            consistencyFindings: [new ConsistencyFinding('test', 'ok')],
            simulationResults: [new FailureSimulationResult('test', 'simulated')],
            summary: 'All sections populated.',
        );

        self::assertCount(1, $report->findings);
        self::assertCount(1, $report->recommendations);
        self::assertCount(1, $report->consistencyFindings);
        self::assertCount(1, $report->simulationResults);
        self::assertNotEmpty($report->summary);
    }
}
