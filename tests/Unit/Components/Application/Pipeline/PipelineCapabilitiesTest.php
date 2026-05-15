<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Pipeline;

use Avax\Components\Application\Pipeline\System\Capabilities\Hooks\PipelineHook;
use Avax\Components\Application\Pipeline\System\Capabilities\Hooks\PipelineStage;
use Avax\Components\Application\Pipeline\System\Capabilities\Hooks\StagePipeline;
use Avax\Components\Application\Pipeline\System\Capabilities\PipelineHooks\HookRegistry;
use Avax\Components\Application\Pipeline\System\PublicSurface\Pipeline;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PipelineCapabilitiesTest extends TestCase
{
    public function test_public_pipeline_executes_registered_hooks_in_registration_order() : void
    {
        Pipeline::setInstance(new HookRegistry());
        Pipeline::beforeController(static fn (string $value) : string => $value . '-first');
        Pipeline::beforeController(static fn (string $value) : string => $value . '-second');

        $this->assertSame('start-first-second', Pipeline::execute('beforeController', 'start'));
    }

    public function test_public_pipeline_returns_original_data_when_hook_is_missing() : void
    {
        Pipeline::setInstance(new HookRegistry());
        $this->assertSame('unchanged', Pipeline::execute('missingHook', 'unchanged'));
    }

    public function test_hook_registry_reports_hook_presence_and_count() : void
    {
        $registry = new HookRegistry();

        $this->assertFalse($registry->has('beforeRoute'));

        $registry->add('beforeRoute', static fn (string $value) : string => $value);
        $registry->add('beforeRoute', static fn (string $value) : string => $value);

        $this->assertTrue($registry->has('beforeRoute'));
        $this->assertSame(2, $registry->count('beforeRoute'));
        $this->assertArrayHasKey('beforeRoute', $registry->all());
    }

    public function test_stage_pipeline_executes_matching_hooks_by_priority() : void
    {
        $pipeline = new StagePipeline();

        $pipeline->register(new PipelineHook(
                                name    : 'beforeRoute',
                                handler : static fn (string $value) : string => $value . '-low',
                                priority: 10,
                            ));
        $pipeline->register(new PipelineHook(
                                name    : 'beforeRoute',
                                handler : static fn (string $value) : string => $value . '-high',
                                priority: 100,
                            ));
        $pipeline->register(new PipelineHook(
                                name    : 'afterRoute',
                                handler : static fn (string $value) : string => $value . '-ignored',
                                priority: 1000,
                            ));

        $this->assertTrue($pipeline->hasHooks('beforeRoute'));
        $this->assertFalse($pipeline->hasHooks('missingStage'));
        $this->assertSame('start-high-low', $pipeline->execute('beforeRoute', 'start'));
    }

    public function test_pipeline_stage_stop_preserves_existing_data_when_no_override_is_passed() : void
    {
        $stage = new PipelineStage(name: 'beforeResponse', data: ['status' => 200]);

        $stopped = $stage->stop();

        $this->assertTrue($stopped->stopped);
        $this->assertSame(['status' => 200], $stopped->data);
    }

    protected function setUp() : void
    {
        Pipeline::reset();
    }
}
