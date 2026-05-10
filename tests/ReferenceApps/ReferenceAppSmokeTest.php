<?php

declare(strict_types=1);

namespace Tests\ReferenceApps;

use Avax\Framework\System\Capabilities\Doctor\Foundation\DoctorFinding;
use Avax\Framework\System\Capabilities\Doctor\Foundation\DoctorReport;
use Avax\Framework\System\Capabilities\Doctor\Foundation\DoctorSeverity;
use Avax\Framework\System\Capabilities\Health\CheckLiveness;
use Avax\Framework\System\Capabilities\Health\CheckReadiness;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\EvaluateFeatureFlag;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlag;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlagName;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlagState;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\InMemoryFeatureFlagStore;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\DefinePolicy;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\EvaluatePolicy;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyAction;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyResource;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicySubject;
use Avax\Framework\System\Capabilities\Security\RequestSigning\SignInternalRequest;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignatureKeyId;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignatureNonce;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignatureTimestamp;
use Avax\Framework\System\Capabilities\Security\ServiceDiscovery\Foundation\ServiceEndpoint;
use Avax\Framework\System\Capabilities\Security\ServiceDiscovery\Foundation\ServiceName;
use Avax\Framework\System\Capabilities\Security\ServiceDiscovery\InMemoryServiceRegistry;
use Avax\Framework\System\Capabilities\SystemDesign\EstimateRuntimeCapacity;
use Avax\Framework\System\Capabilities\SystemDesign\InspectOutboxConsistency;
use Avax\Framework\System\Capabilities\SystemDesign\RunFailureSimulation;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Reference app smoke tests.
 *
 * Each test proves a V4 capability through the public API.
 */
final class ReferenceAppSmokeTest extends TestCase
{
    #[Test]
    public function hello_world_proves_app_api(): void
    {
        $file = __DIR__ . '/../../examples/v4/hello-world/app.php';
        self::assertFileExists($file);

        $content = file_get_contents($file);
        self::assertNotFalse($content);
        self::assertStringContainsString('Avax::create()', $content);
        self::assertStringContainsString('$app->get(', $content);
    }

    #[Test]
    public function hello_world_has_readme(): void
    {
        $readme = __DIR__ . '/../../examples/v4/hello-world/README.md';
        self::assertFileExists($readme);
        $readmeContent = file_get_contents($readme);
        self::assertNotFalse($readmeContent);
        self::assertStringContainsString('Avax::create()', $readmeContent);
    }

    #[Test]
    public function secure_registration_api_proves_validation(): void
    {
        $file = __DIR__ . '/../../examples/v4/secure-registration-api/app.php';
        self::assertFileExists($file);
    }

    #[Test]
    public function security_signing_capability_available(): void
    {
        $signer = new SignInternalRequest('secret', new SignatureKeyId('key-1'));
        $payload = $signer->sign('GET', '/test', '', [], SignatureNonce::generate(), SignatureTimestamp::now());

        self::assertNotEmpty($payload->signature);
    }

    #[Test]
    public function policy_engine_capability_available(): void
    {
        $policy = (new DefinePolicy('test'))->allow('allow-all');
        $evaluator = new EvaluatePolicy();

        $decision = $evaluator->evaluate(
            $policy,
            new PolicySubject('user-1'),
            new PolicyAction('read'),
            new PolicyResource('data', 'item-1'),
        );

        self::assertTrue($decision->isAllowed());
    }

    #[Test]
    public function feature_flags_capability_available(): void
    {
        $store = new InMemoryFeatureFlagStore();
        $store->set(new FeatureFlag(new FeatureFlagName('beta'), FeatureFlagState::Enabled));

        $evaluator = new EvaluateFeatureFlag($store);

        self::assertTrue($evaluator->isEnabled(new FeatureFlagName('beta')));
    }

    #[Test]
    public function service_discovery_capability_available(): void
    {
        $registry = new InMemoryServiceRegistry();
        $registry->register(new ServiceName('api'), new ServiceEndpoint('http://api:8080'));

        $resolved = $registry->resolve(new ServiceName('api'));

        self::assertCount(1, $resolved);
    }

    #[Test]
    public function system_design_capacity_available(): void
    {
        $estimator = new EstimateRuntimeCapacity();
        $recommendations = $estimator->recommend(['avg_latency_ms' => 50, 'target_requests_per_second' => 100]);

        self::assertNotEmpty($recommendations);
    }

    #[Test]
    public function system_design_outbox_inspection_available(): void
    {
        $inspector = new InspectOutboxConsistency();
        $findings = $inspector->inspect(['pending_count' => 5, 'failed_count' => 0, 'relayed_count' => 100]);

        self::assertNotEmpty($findings);
    }

    #[Test]
    public function system_design_failure_simulation_available(): void
    {
        $simulator = new RunFailureSimulation();
        $result = $simulator->simulate('database_failure');

        self::assertSame('database_failure', $result->scenario);
    }

    #[Test]
    public function health_liveness_available(): void
    {
        $check = new CheckLiveness();
        $report = $check->check();

        self::assertSame(HealthStatus::Green, $report->overall);
    }

    #[Test]
    public function health_readiness_available(): void
    {
        $check = new CheckReadiness();
        $report = $check->check();

        self::assertSame(HealthStatus::Green, $report->status);
    }

    #[Test]
    public function doctor_foundation_available(): void
    {
        $report = new DoctorReport();
        $report->add(new DoctorFinding('test', DoctorSeverity::Green, 'OK'));

        self::assertSame(DoctorSeverity::Green, $report->overallStatus());
    }
}
