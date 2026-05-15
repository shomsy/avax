<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Pipeline;

use Avax\Components\Application\Pipeline\System\Capabilities\PipelineHooks\HookRegistry;
use Avax\Components\Application\Pipeline\System\PublicSurface\Pipeline;
use Closure;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PipelineLifecycleTest extends TestCase
{
    protected function tearDown(): void
    {
        Pipeline::reset();
    }

    public function test_unconfigured_usage_fails_clearly() : void
    {
        Pipeline::reset();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Pipeline registry not configured');

        Pipeline::hooks();
    }

    public function test_setInstance_wires_registry() : void
    {
        $custom = new HookRegistry();
        Pipeline::setInstance($custom);

        // Add hook through the facade — it should use our custom registry
        Pipeline::beforeRoute(fn () => 'custom');

        // Verify the custom registry received the hook
        $hooks = $custom->all();
        $this->assertTrue(isset($hooks['beforeRoute']));
    }

    public function test_double_boot_fails() : void
    {
        Pipeline::setInstance(new HookRegistry());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already configured');

        Pipeline::setInstance(new HookRegistry());
    }

    public function test_reset_clears_static_state() : void
    {
        Pipeline::setInstance(new HookRegistry());
        Pipeline::beforeRoute(fn () => 'test');
        $this->assertNotEmpty(Pipeline::hooks());

        Pipeline::reset();

        // After reset, usage should fail clearly
        $this->expectException(RuntimeException::class);
        Pipeline::hooks();
    }

    public function test_reset_provides_test_isolation() : void
    {
        // Simulate test A adding hooks
        Pipeline::setInstance(new HookRegistry());
        Pipeline::beforeRoute(fn () => 'test-a');
        Pipeline::afterResponse(fn () => 'test-a-2');
        $this->assertCount(2, Pipeline::hooks());

        // Simulate test B starting after reset
        Pipeline::reset();

        // Test B's first usage should fail until it configures its own registry
        $this->expectException(RuntimeException::class);
        Pipeline::hooks();
    }

    public function test_hooks_execute_in_order() : void
    {
        Pipeline::setInstance(new HookRegistry());

        $results = [];
        Pipeline::beforeRoute(static function () use (&$results) {
            $results[] = 'first';
        });
        Pipeline::beforeRoute(static function () use (&$results) {
            $results[] = 'second';
        });

        Pipeline::execute('beforeRoute');

        $this->assertSame(['first', 'second'], $results);
    }
}
