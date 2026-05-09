<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesignKit\ReferenceArchitecture;

use Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\CapacityModel;
use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\MessagingModel;
use Avax\Labs\SystemDesignKit\System\Capabilities\SchemaValidation\NativeYamlParser;
use Avax\Labs\SystemDesignKit\System\PublicSurface\SystemDesignKit;
use PHPUnit\Framework\TestCase;

/**
 * End-to-end tests that validate complete reference architectures
 * through the SystemDesignKit public API.
 *
 * These tests prove that:
 * - Reference architecture YAML files are valid
 * - Capacity models parse and validate correctly
 * - Scenarios run and produce results
 * - Architecture tests evaluate with meaningful assertions
 * - Failure simulations detect real violations
 *
 * @group v3
 */
final class ReferenceArchitectureTest extends TestCase
{
    private string $urlShortenerDir;
    private string $eCommerceDir;

    public function test_url_shortener_capacity_valid() : void
    {
        $result = SystemDesignKit::validateCapacity($this->urlShortenerDir . '/capacity.yaml');

        self::assertTrue($result['valid'], 'URL shortener capacity should be valid');
        self::assertNotNull($result['model']);
        self::assertSame('url-shortener', $result['model']->system);
    }

    public function test_ecommerce_capacity_valid() : void
    {
        $result = SystemDesignKit::validateCapacity($this->eCommerceDir . '/capacity.yaml');

        self::assertTrue($result['valid'], 'E-commerce capacity should be valid');
        self::assertNotNull($result['model']);
        self::assertSame('e-commerce', $result['model']->system);
    }

    public function test_url_shortener_scenarios_run() : void
    {
        $capacityResult = SystemDesignKit::validateCapacity($this->urlShortenerDir . '/capacity.yaml');
        $model          = $capacityResult['model'];

        self::assertNotNull($model, 'Capacity model must be valid to run scenarios');

        $result = SystemDesignKit::runScenarios($this->urlShortenerDir . '/scenarios.yaml', $model);

        self::assertGreaterThan(0, $result['total'], 'Should have at least 1 scenario');
        self::assertArrayHasKey('passed', $result);
        self::assertArrayHasKey('failed', $result);

        foreach ($result['scenarios'] as $scenario) {
            self::assertArrayHasKey('scenario', $scenario);
            self::assertArrayHasKey('passed', $scenario);
            self::assertArrayHasKey('results', $scenario);
        }
    }

    public function test_ecommerce_scenarios_run() : void
    {
        $capacityResult = SystemDesignKit::validateCapacity($this->eCommerceDir . '/capacity.yaml');
        $model          = $capacityResult['model'];

        self::assertNotNull($model);

        $result = SystemDesignKit::runScenarios($this->eCommerceDir . '/scenarios.yaml', $model);

        self::assertGreaterThan(0, $result['total']);
        self::assertArrayHasKey('passed', $result);
    }

    public function test_url_shortener_architecture_tests_run() : void
    {
        $capacityResult = SystemDesignKit::validateCapacity($this->urlShortenerDir . '/capacity.yaml');
        $model          = $capacityResult['model'];

        self::assertNotNull($model);

        $messagingConfig = (new NativeYamlParser())
            ->parseFile($this->urlShortenerDir . '/capacity.yaml');
        $messagingModel  = isset($messagingConfig['messaging'])
            ? MessagingModel::fromConfig($messagingConfig)
            : null;

        $result = SystemDesignKit::runArchitectureTests(
            $this->urlShortenerDir . '/architecture-tests.yaml',
            $model,
            $messagingModel,
        );

        self::assertGreaterThan(0, $result['total']);
        self::assertArrayHasKey('passed', $result);
        self::assertArrayHasKey('failed', $result);
        self::assertArrayHasKey('critical_failures', $result);

        foreach ($result['tests'] as $test) {
            self::assertArrayHasKey('test', $test);
            self::assertArrayHasKey('severity', $test);
            self::assertArrayHasKey('passed', $test);
            self::assertArrayHasKey('detail', $test);
            self::assertNotEmpty($test['detail'], "Test {$test['test']} must have meaningful detail");
        }
    }

    public function test_ecommerce_architecture_tests_run() : void
    {
        $capacityResult = SystemDesignKit::validateCapacity($this->eCommerceDir . '/capacity.yaml');
        $model          = $capacityResult['model'];

        self::assertNotNull($model);

        $messagingConfig = (new NativeYamlParser())
            ->parseFile($this->eCommerceDir . '/capacity.yaml');
        $messagingModel  = isset($messagingConfig['messaging'])
            ? MessagingModel::fromConfig($messagingConfig)
            : null;

        $result = SystemDesignKit::runArchitectureTests(
            $this->eCommerceDir . '/architecture-tests.yaml',
            $model,
            $messagingModel,
        );

        self::assertGreaterThan(0, $result['total']);
    }

    public function test_url_shortener_failure_simulations() : void
    {
        $capacityResult = SystemDesignKit::validateCapacity($this->urlShortenerDir . '/capacity.yaml');
        $model          = $capacityResult['model'];

        self::assertNotNull($model);

        $result = SystemDesignKit::runFailureSimulations($model);

        self::assertGreaterThan(0, $result['total']);
        self::assertArrayHasKey('violations_detected', $result);
        self::assertArrayHasKey('clean', $result);

        foreach ($result['simulations'] as $sim) {
            self::assertArrayHasKey('simulation', $sim);
            self::assertArrayHasKey('failure_mode', $sim);
            self::assertArrayHasKey('violation_detected', $sim);
        }
    }

    public function test_ecommerce_failure_simulations() : void
    {
        $capacityResult = SystemDesignKit::validateCapacity($this->eCommerceDir . '/capacity.yaml');
        $model          = $capacityResult['model'];

        self::assertNotNull($model);

        $result = SystemDesignKit::runFailureSimulations($model);

        self::assertGreaterThan(0, $result['total']);
    }

    public function test_url_shortener_full_validation_passes() : void
    {
        $result = SystemDesignKit::validateReferenceArchitecture($this->urlShortenerDir);

        self::assertTrue($result['capacity_valid']);
        self::assertGreaterThan(0, $result['scenarios_total']);
        self::assertGreaterThan(0, $result['arch_tests_total']);
        self::assertGreaterThan(0, $result['failure_simulations_total']);
        self::assertTrue($result['overall_pass']);
    }

    public function test_ecommerce_full_validation_passes() : void
    {
        $result = SystemDesignKit::validateReferenceArchitecture($this->eCommerceDir);

        self::assertTrue($result['capacity_valid']);
        self::assertGreaterThan(0, $result['scenarios_total']);
        self::assertGreaterThan(0, $result['arch_tests_total']);
        self::assertGreaterThan(0, $result['failure_simulations_total']);
        self::assertTrue($result['overall_pass']);
    }

    public function test_both_reference_architectures_pass() : void
    {
        $urlShortener = SystemDesignKit::validateReferenceArchitecture($this->urlShortenerDir);
        $eCommerce    = SystemDesignKit::validateReferenceArchitecture($this->eCommerceDir);

        self::assertTrue($urlShortener['overall_pass'], 'URL shortener must pass');
        self::assertTrue($eCommerce['overall_pass'], 'E-commerce must pass');

        self::assertSame(0, $urlShortener['scenarios_failed']);
        self::assertSame(0, $urlShortener['arch_tests_critical_failures']);
        self::assertSame(0, $eCommerce['scenarios_failed']);
        self::assertSame(0, $eCommerce['arch_tests_critical_failures']);
    }

    public function test_failure_simulations_detect_real_violations() : void
    {
        // Create an intentionally bad capacity model
        $badModel = CapacityModel::fromConfig([
                                                                                                          'system'       => 'bad-system',
                                                                                                          'traffic'      => [
                                                                                                              'requests_per_second' => 10000,
                                                                                                              'reads_per_second'    => 1000,
                                                                                                              'writes_per_second'   => 9000,
                                                                                                              'read_write_ratio'    => 0,
                                                                                                              'peak_multiplier'     => 1,
                                                                                                          ],
                                                                                                          'storage'      => [
                                                                                                              'growth_per_day'            => 1000000,
                                                                                                              'average_record_size_bytes' => 512,
                                                                                                              'retention_days'            => 365,
                                                                                                          ],
                                                                                                          'cache'        => [
                                                                                                              'hit_ratio_target'             => 0.5,
                                                                                                              'stampede_protection_required' => false,
                                                                                                          ],
                                                                                                          'queue'        => [
                                                                                                              'max_depth'                      => 1000,
                                                                                                              'consumer_throughput_per_second' => 100,
                                                                                                          ],
                                                                                                          'latency'      => [
                                                                                                              'p50_ms' => 500,
                                                                                                              'p95_ms' => 3000,
                                                                                                              'p99_ms' => 8000,
                                                                                                          ],
                                                                                                          'availability' => [
                                                                                                              'slo'                              => 99.0,
                                                                                                              'failure_budget_minutes_per_month' => 100.0,
                                                                                                          ],
                                                                                                      ]);

        $result = SystemDesignKit::runFailureSimulations($badModel);

        // This bad system should have multiple violations detected
        self::assertGreaterThan(0, $result['violations_detected'],
                                'Bad capacity model should have failure violations');
    }

    public function test_reference_architecture_has_at_least_3_failure_scenarios() : void
    {
        $result = SystemDesignKit::validateReferenceArchitecture($this->urlShortenerDir);

        self::assertGreaterThanOrEqual(3, $result['failure_simulations_total'],
                                       'Must have at least 3 failure scenarios');
    }

    public function test_at_least_2_reference_architectures_validate() : void
    {
        $urlShortener = SystemDesignKit::validateReferenceArchitecture($this->urlShortenerDir);
        $eCommerce    = SystemDesignKit::validateReferenceArchitecture($this->eCommerceDir);

        $passingCount = 0;

        if ($urlShortener['overall_pass']) {
            $passingCount++;
        }

        if ($eCommerce['overall_pass']) {
            $passingCount++;
        }

        self::assertGreaterThanOrEqual(2, $passingCount,
                                       'At least 2 reference architectures must validate');
    }

    protected function setUp() : void
    {
        $this->urlShortenerDir = __DIR__ . '/../../../labs/SystemDesignKit/reference-architectures/url-shortener';
        $this->eCommerceDir    = __DIR__ . '/../../../labs/SystemDesignKit/reference-architectures/e-commerce';
    }
}
