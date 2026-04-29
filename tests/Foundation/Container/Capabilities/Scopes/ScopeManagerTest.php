<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Container\Capabilities\Scopes;

use Avax\Tests\TestCase;
use components\Container\DependencyInjection\Capability\Scopes\ScopeManager;
use components\Container\DependencyInjection\Capability\Scopes\ScopeRegistry;
use RuntimeException;
use stdClass;

final class ScopeManagerTest extends TestCase
{
    public function test_scope_manager_stores_singletons_when_no_scope_is_active() : void
    {
        $registry = new ScopeRegistry;
        $manager  = new ScopeManager(registry: $registry);
        $service  = new stdClass;

        $manager->set(abstract: 'service', instance: $service);

        $this->assertTrue(condition: $registry->has(abstract: 'service'));
        $this->assertSame(expected: $service, actual: $registry->get(abstract: 'service'));
    }

    public function test_begin_and_end_scope_isolate_scoped_instances() : void
    {
        $registry  = new ScopeRegistry;
        $manager   = new ScopeManager(registry: $registry);
        $singleton = new stdClass;
        $scoped    = new stdClass;

        $manager->set(abstract: 'service', instance: $singleton);

        $manager->beginScope();
        $manager->set(abstract: 'service', instance: $scoped);

        $this->assertSame(expected: $scoped, actual: $registry->get(abstract: 'service'));

        $manager->endScope();

        $this->assertSame(expected: $singleton, actual: $registry->get(abstract: 'service'));
    }

    public function test_end_scope_without_active_scope_throws() : void
    {
        $manager = new ScopeManager(registry: new ScopeRegistry);

        $this->expectException(exception: RuntimeException::class);
        $manager->endScope();
    }

    public function test_terminate_clears_registry_state() : void
    {
        $registry = new ScopeRegistry;
        $manager  = new ScopeManager(registry: $registry);

        $manager->set(abstract: 'service', instance: new stdClass);
        $manager->beginScope();
        $manager->set(abstract: 'scoped', instance: new stdClass);
        $manager->terminate();

        $this->assertFalse(condition: $registry->has(abstract: 'service'));
        $this->assertFalse(condition: $registry->has(abstract: 'scoped'));
    }
}
