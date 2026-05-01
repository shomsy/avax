<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\GoldenPath;

use Avax\Components\Application\FeatureFlags\System\PublicSurface\FeatureFlags;
use Avax\Components\Application\Pipeline\System\PublicSurface\Pipeline;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy;
use Avax\Components\Operations\Concurrency\System\PublicSurface\Concurrency;
use Avax\Components\Operations\Resilience\System\Capabilities\Fallback\System\PublicSurface\Fallback;
use Avax\Components\Security\System\System\PublicSurface\Security;
use Avax\Tests\TestCase;
use RuntimeException;

/**
 * GOLDEN PATH INTEGRATION TESTS - Complete Component Tests
 */
class GoldenPathTest extends TestCase
{
    /**
     * @test
     */
    public function feature_flags_work() : void
    {
        FeatureFlags::enable('new_dashboard');
        $this->assertTrue(FeatureFlags::enabled('new_dashboard'));

        FeatureFlags::disable('new_dashboard');
        $this->assertFalse(FeatureFlags::enabled('new_dashboard'));

        $variant = FeatureFlags::variant('new_dashboard');
        $this->assertIsString($variant);
    }

    /**
     * @test
     */
    public function tenancy_isolation() : void
    {
        Tenancy::setTenantId('tenant_123');
        $this->assertEquals('tenant_123', Tenancy::getTenantId());

        Tenancy::clearTenant();
        $this->assertNull(Tenancy::getTenantId());

        $result = Tenancy::run('tenant_456', static fn () => 'executed');
        $this->assertEquals('executed', $result);
    }

    /**
     * @test
     */
    public function pipeline_hooks() : void
    {
        Pipeline::beforeController(static fn ($r) => $r);
        Pipeline::afterController(static fn ($r) => $r);

        $hooks = Pipeline::hooks();

        $this->assertIsArray($hooks);
        $this->assertTrue(Pipeline::hooks()['beforeController'] ?? false ? true : false);
    }

    /**
     * @test
     */
    public function fallback_chain() : void
    {
        $callCount = 0;

        $result = Fallback::execute(
            fallbacks: [
                           static function () use (&$callCount) : void {
                               $callCount++;

                               throw new RuntimeException('Primary failed');
                           },
                           static function () use (&$callCount) {
                               $callCount++;

                               return 'fallback_result';
                           },
                       ],
        );

        $this->assertEquals('fallback_result', $result);
        $this->assertEquals(2, $callCount);
    }

    /**
     * @test
     */
    public function security_hash_verify() : void
    {
        $password = 'test_password_123';
        $hashed = Security::hash($password);

        $this->assertTrue(Security::verify($password, $hashed));
        $this->assertFalse(Security::verify('wrong_password', $hashed));
    }

    /**
     * @test
     */
    public function security_generate_token() : void
    {
        $token = Security::generateToken();

        $this->assertIsString($token);
        $this->assertEquals(32, strlen($token));
    }

    /**
     * @test
     */
    public function concurrency_parallel() : void
    {
        $results = Concurrency::run(
            tasks        : [
                               static fn () => 'task1',
                               static fn () => 'task2',
                               static fn () => 'task3',
                           ],
            maxConcurrent: 3,
        );

        $this->assertCount(3, $results);
    }
}
