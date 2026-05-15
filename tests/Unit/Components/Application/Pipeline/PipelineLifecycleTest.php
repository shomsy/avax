<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Pipeline;

use Avax\Components\Application\Pipeline\System\PublicSurface\HookRegistry;
use Avax\Components\Application\Pipeline\System\PublicSurface\Pipeline;
use Closure;
use PHPUnit\Framework\TestCase;

final class PipelineLifecycleTest extends TestCase
{
    protected function tearDown(): void
    {
        Pipeline::reset();
    }

    public function test_reset_clears_static_hook_registry() : void
    {
        // Add hooks
        Pipeline::beforeRoute(fn () => 'test');
        $this->assertNotEmpty(Pipeline::hooks());

        // Reset should clear all hooks
        Pipeline::reset();
        $this->assertSame([], Pipeline::hooks());
    }

    public function test_setInstance_replaces_registry() : void
    {
        Pipeline::reset();
        $custom = new HookRegistry();
        Pipeline::setInstance($custom);

        // Add hook through the facade — it should use our custom registry
        Pipeline::beforeRoute(fn () => 'custom');

        // Verify the custom registry received the hook
        /** @var array<string, list<\Closure>> $hooks */
        $hooks = $custom->all();
        $this->assertTrue(isset($hooks['beforeRoute']));
    }

    public function test_reset_provides_test_isolation() : void
    {
        // Simulate test A adding hooks
        Pipeline::beforeRoute(fn () => 'test-a');
        Pipeline::afterResponse(fn () => 'test-a-2');
        $this->assertCount(2, Pipeline::hooks());

        // Simulate test B starting after reset
        Pipeline::reset();
        $this->assertSame([], Pipeline::hooks());
    }

    public function test_hooks_execute_in_order() : void
    {
        Pipeline::reset();

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
