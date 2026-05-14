<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\PublicSurface;

use Avax\Components\SystemDesign\System\Capabilities\Capacity\CapacityModel;
use Avax\Components\SystemDesign\System\Capabilities\Consistency\ConsistencyModel;
use Avax\Components\SystemDesign\System\Capabilities\Messaging\MessagingModel;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\NativeYamlParser;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\SchemaValidator;
use Avax\Components\SystemDesign\System\Flows\DetectConsistencyRisk\DetectConsistencyRisk;
use Avax\Components\SystemDesign\System\Flows\DetectMessagingRisk\DetectMessagingRisk;
use Avax\Components\SystemDesign\System\Flows\EstimateCacheEffectiveness\EstimateCacheEffectiveness;
use Avax\Components\SystemDesign\System\Flows\EstimateFailureBudget\EstimateFailureBudget;
use Avax\Components\SystemDesign\System\Flows\EstimateLatencyBudget\EstimateLatencyBudget;
use Avax\Components\SystemDesign\System\Flows\EstimateProjectionLag\EstimateProjectionLag;
use Avax\Components\SystemDesign\System\Flows\EstimateQueuePressure\EstimateQueuePressure;
use Avax\Components\SystemDesign\System\Flows\EstimateReplicationLag\EstimateReplicationLag;
use Avax\Components\SystemDesign\System\Flows\EstimateStorageGrowth\EstimateStorageGrowth;
use Avax\Components\SystemDesign\System\Flows\EstimateTrafficLoad\EstimateTrafficLoad;
use Avax\Components\SystemDesign\System\Flows\ExplainConsistencyTradeoff\ExplainConsistencyTradeoff;
use Avax\Components\SystemDesign\System\Flows\ResolveConflict\ResolveConflict;
use Avax\Components\SystemDesign\System\Flows\RunArchitectureTests\RunArchitectureTests;
use Avax\Components\SystemDesign\System\Flows\RunFailureSimulations\RunFailureSimulations;
use Avax\Components\SystemDesign\System\Flows\RunScenarios\RunScenarios;
use Avax\Components\SystemDesign\System\Flows\ValidateCapacityModel\ValidateCapacityModel;
use Avax\Components\SystemDesign\System\Flows\ValidateConsistencyModel\ValidateConsistencyModel;
use Avax\Components\SystemDesign\System\Flows\ValidateMessagingModel\ValidateMessagingModel;

/**
 * SystemDesignKit — V3 Executable System Design Framework.
 *
 * Status: @public
 * Placement: labs/ (V3 experimental promoted to public API)
 *
 * Purpose: model, validate, simulate, test, and explain
 * large application architectures.
 *
 * V3 does not just build applications.
 * V3 tests whether the architecture makes sense.
 */
final readonly class SystemDesignKit
{
    private string $schemaDir;

    public function __construct(
        private NativeYamlParser $yamlParser,
        private SchemaValidator $schemaValidator,
    ) {
        $this->schemaDir = __DIR__ . '/../../schemas';
    }

    private function validateCapacityModel() : ValidateCapacityModel
    {
        return new ValidateCapacityModel(
            schemaValidator: $this->schemaValidator,
            schemaDir      : $this->schemaDir,
            yamlParser     : $this->yamlParser,
        );
    }

    private function runScenariosFlow() : RunScenarios
    {
        return new RunScenarios(yamlParser: $this->yamlParser);
    }

    private function runArchitectureTestsFlow() : RunArchitectureTests
    {
        return new RunArchitectureTests(yamlParser: $this->yamlParser);
    }

    /**
     * Validate a capacity.yaml file (schema + model validation).
     *
     * @return array{valid: bool, schema_errors: list<string>, model_errors?: list<string>, model: ?CapacityModel}
     */
    public function validateCapacity(string $capacityPath) : array
    {
        return $this->validateCapacityModel()->execute($capacityPath);
    }

    /**
     * Estimate traffic load from a capacity model.
     *
     * @return array{
     *     peak_rps: int,
     *     read_rps: int,
     *     write_rps: int,
     *     read_ratio: float,
     *     write_ratio: float,
     *     cache_hits_per_second: float,
     *     cache_misses_per_second: float,
     *     backend_rps: float,
     * }
     */
    public function estimateTrafficLoad(CapacityModel $model) : array
    {
        return (new EstimateTrafficLoad())->execute($model);
    }

    /**
     * Estimate storage growth from a capacity model.
     *
     * @return array{
     *     daily_growth_bytes: int,
     *     daily_growth_human: string,
     *     retention_bytes: int,
     *     retention_human: string,
     *     yearly_growth_bytes: int,
     *     yearly_growth_human: string,
     * }
     */
    public function estimateStorageGrowth(CapacityModel $model) : array
    {
        return (new EstimateStorageGrowth())->execute($model);
    }

    /**
     * Estimate cache effectiveness from a capacity model.
     *
     * @return array{
     *     hit_ratio: float,
     *     miss_ratio: float,
     *     hits_per_second: float,
     *     misses_per_second: float,
     *     daily_hits: float,
     *     daily_misses: float,
     *     cache_savings_ratio: string,
     * }
     */
    public function estimateCacheEffectiveness(CapacityModel $model) : array
    {
        return (new EstimateCacheEffectiveness())->execute($model);
    }

    /**
     * Estimate queue pressure from a capacity model.
     *
     * @return array{
     *     write_rps: int,
     *     consumer_throughput: int,
     *     consumer_count: int,
     *     required_consumers: int,
     *     can_handle_load: bool,
     *     queue_utilization: float,
     *     consumers_underprovisioned: bool,
     * }
     */
    public function estimateQueuePressure(CapacityModel $model) : array
    {
        return (new EstimateQueuePressure())->execute($model);
    }

    /**
     * Estimate latency budget allocation from a capacity model.
     *
     * @return array{
     *     p50_budget_ms: int,
     *     p95_budget_ms: int,
     *     p99_budget_ms: int,
     *     allocation: array<string, array{p50_ms: float, p95_ms: float, p99_ms: float}>,
     *     budget_valid: bool,
     *     budget_violations: list<string>,
     * }
     */
    public function estimateLatencyBudget(CapacityModel $model) : array
    {
        return (new EstimateLatencyBudget())->execute($model);
    }

    /**
     * Estimate failure budget from a capacity model.
     *
     * @return array{
     *     slo_percentage: float,
     *     monthly_downtime_minutes: float,
     *     monthly_downtime_human: string,
     *     yearly_downtime_minutes: float,
     *     yearly_downtime_human: string,
     *     failure_budget_minutes: float,
     *     budget_exhausted_at_incidents: int,
     * }
     */
    public function estimateFailureBudget(CapacityModel $model) : array
    {
        return (new EstimateFailureBudget())->execute($model);
    }

    /**
     * Validate a consistency model.
     *
     * @param array<int|string, mixed> $config
     *
     * @return array{valid: bool, errors: list<string>, model: ?ConsistencyModel}
     */
    public function validateConsistency(array $config) : array
    {
        return (new ValidateConsistencyModel())->execute($config);
    }

    /**
     * Explain consistency tradeoffs for each path.
     *
     * @return array{
     *     system: string,
     *     total_latency_overhead_ms: int,
     *     paths: list<array{
     *         path: string,
     *         model: string,
     *         overhead_ms: int,
     *         stale_read_risk: string,
     *         staleness_tolerance_ms: int,
     *         tradeoff: string,
     *     }>,
     *     risks: array{
     *         high_stale_read_paths: list<string>,
     *         high_duplicate_risk_paths: list<string>,
     *         data_loss_risk_paths: list<string>,
     *     },
     * }
     */
    public function explainConsistencyTradeoff(ConsistencyModel $model) : array
    {
        return (new ExplainConsistencyTradeoff())->execute($model);
    }

    /**
     * Detect consistency risks.
     *
     * @return list<array{
     *     severity: string,
     *     category: string,
     *     description: string,
     *     path: string,
     *     recommendation: string,
     * }>
     */
    public function detectConsistencyRisk(ConsistencyModel $model) : array
    {
        return (new DetectConsistencyRisk())->execute($model);
    }

    /**
     * Estimate projection lag.
     *
     * @return array{
     *     projections: list<array{
     *         name: string,
     *         expected_ms: int,
     *         p95_ms: int,
     *         p99_ms: int,
     *         within_budget: bool,
     *     }>,
     *     max_p99_ms: int,
     *     projection_count: int,
     * }
     */
    public function estimateProjectionLag(ConsistencyModel $model, int $freshnessThresholdMs = 1000) : array
    {
        return (new EstimateProjectionLag())->execute($model, $freshnessThresholdMs);
    }

    /**
     * Estimate replication lag.
     *
     * @return array{
     *     has_replication: bool,
     *     expected_ms: int,
     *     p95_ms: int,
     *     p99_ms: int,
     *     replica_count: int,
     *     topology: string,
     *     worst_case_staleness_ms: int,
     *     budgets_exceeded: list<array{
     *         path: string,
     *         budget_ms: int,
     *         exceeded_by_ms: int,
     *     }>,
     * }
     */
    public function estimateReplicationLag(ConsistencyModel $model) : array
    {
        return (new EstimateReplicationLag())->execute($model);
    }

    /**
     * Analyze conflict resolution.
     *
     * @return array{
     *     conflicts: list<array{
     *         path: string,
     *         strategy: string,
     *         conflicts_per_1000_writes: float,
     *         data_loss_risk: bool,
     *         complexity: string,
     *     }>,
     *     total_data_loss_risk_paths: list<string>,
     *     total_complexity_cost: string,
     * }
     */
    public function resolveConflict(ConsistencyModel $model) : array
    {
        return (new ResolveConflict())->execute($model);
    }

    /**
     * Validate a messaging model.
     *
     * @param array<int|string, mixed> $config
     *
     * @return array{valid: bool, errors: list<string>, model: ?MessagingModel}
     */
    public function validateMessaging(array $config) : array
    {
        return (new ValidateMessagingModel())->execute($config);
    }

    /**
     * Detect messaging risks.
     *
     * @return list<array{
     *     severity: string,
     *     category: string,
     *     description: string,
     *     path: string,
     *     recommendation: string,
     * }>
     */
    public function detectMessagingRisk(MessagingModel $model) : array
    {
        return (new DetectMessagingRisk())->execute($model);
    }

    /**
     * Run scenarios from a scenarios.yaml file against a capacity model.
     *
     * @return array{
     *     file: string,
     *     total: int,
     *     passed: int,
     *     failed: int,
     *     scenarios: list<array{
     *         scenario: string,
     *         type: string,
     *         passed: bool,
     *         results: list<array{assertion: string, passed: bool, detail: string}>,
     *     }>,
     * }
     */
    public function runScenarios(string $scenariosPath, CapacityModel $model) : array
    {
        return $this->runScenariosFlow()->execute($scenariosPath, $model);
    }

    /**
     * Run architecture tests against capacity and messaging models.
     *
     * @return array{
     *     file: string,
     *     total: int,
     *     passed: int,
     *     failed: int,
     *     critical_failures: int,
     *     tests: list<array{
     *         test: string,
     *         severity: string,
     *         passed: bool,
     *         detail: string,
     *     }>,
     * }
     */
    public function runArchitectureTests(
        string              $testsPath,
        CapacityModel       $capacity,
        MessagingModel|null $messaging = null,
    ) : array
    {
        return $this->runArchitectureTestsFlow()->execute($testsPath, $capacity, $messaging);
    }

    /**
     * Run failure simulations against a capacity model.
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
    public function runFailureSimulations(CapacityModel $model, array|null $customFailures = null) : array
    {
        return (new RunFailureSimulations())->execute($model, $customFailures);
    }

    /**
     * Validate a complete reference architecture.
     *
     * @param string $referenceArchDir Path to reference architecture directory containing capacity.yaml,
     *                                 scenarios.yaml, architecture-tests.yaml
     *
     * @return array{
     *     reference_architecture: string,
     *     capacity_valid: bool,
     *     scenarios_total: int,
     *     scenarios_passed: int,
     *     scenarios_failed: int,
     *     arch_tests_total: int,
     *     arch_tests_passed: int,
     *     arch_tests_failed: int,
     *     arch_tests_critical_failures: int,
     *     failure_simulations_total: int,
     *     failure_simulations_violations: int,
     *     failure_simulations_clean: int,
     *     overall_pass: bool,
     * }
     */
    public function validateReferenceArchitecture(string $referenceArchDir) : array
    {
        $capacityPath     = $referenceArchDir . '/capacity.yaml';
        $scenariosPath    = $referenceArchDir . '/scenarios.yaml';
        $architecturePath = $referenceArchDir . '/architecture-tests.yaml';

        // Validate capacity
        $capacityResult = $this->validateCapacity($capacityPath);
        $capacityValid  = $capacityResult['valid'];
        $capacityModel  = $capacityResult['model'];

        // Parse messaging if available
        $capacityConfig = $this->yamlParser->parseFile($capacityPath);
        $messagingModel = isset($capacityConfig['messaging'])
            ? MessagingModel::fromConfig($capacityConfig)
            : null;

        // Run scenarios
        $scenariosResult = ['total' => 0, 'passed' => 0, 'failed' => 0, 'scenarios' => []];

        if ($capacityModel !== null && file_exists($scenariosPath)) {
            $scenariosResult = $this->runScenarios($scenariosPath, $capacityModel);
        }

        // Run architecture tests
        $archTestsResult = ['total' => 0, 'passed' => 0, 'failed' => 0, 'critical_failures' => 0, 'tests' => []];

        if ($capacityModel !== null && file_exists($architecturePath)) {
            $archTestsResult = $this->runArchitectureTests($architecturePath, $capacityModel, $messagingModel);
        }

        // Run failure simulations
        $failureResult = ['total' => 0, 'violations_detected' => 0, 'clean' => 0, 'simulations' => []];

        if ($capacityModel !== null) {
            $failureResult = $this->runFailureSimulations($capacityModel);
        }

        $overallPass = $capacityValid
            && $scenariosResult['failed'] === 0
            && $archTestsResult['critical_failures'] === 0;

        return [
            'reference_architecture'         => $referenceArchDir,
            'capacity_valid'                 => $capacityValid,
            'scenarios_total'                => $scenariosResult['total'],
            'scenarios_passed'               => $scenariosResult['passed'],
            'scenarios_failed'               => $scenariosResult['failed'],
            'arch_tests_total'               => $archTestsResult['total'],
            'arch_tests_passed'              => $archTestsResult['passed'],
            'arch_tests_failed'              => $archTestsResult['failed'],
            'arch_tests_critical_failures'   => $archTestsResult['critical_failures'],
            'failure_simulations_total'      => $failureResult['total'],
            'failure_simulations_violations' => $failureResult['violations_detected'],
            'failure_simulations_clean'      => $failureResult['clean'],
            'overall_pass'                   => $overallPass,
        ];
    }
}
