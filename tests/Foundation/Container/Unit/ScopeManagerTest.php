<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Features\Operate\Scope\ScopeManager;
use Avax\Container\Features\Operate\Scope\ScopeRegistry;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Unit/ScopeManagerTest.md#quick-summary
 */
final class ScopeRegistryTest extends TestCase
{
    public function test_set_scoped_without_active_scope_throws(): void
    {
        $registry = new ScopeRegistry;

        $this->expectException(exception: RuntimeException::class);

        $registry->setScoped(abstract: 'service', instance: new stdClass);
    }

    public function test_begin_and_end_scope_controls_scoped_instances(): void
    {
        $registry = new ScopeRegistry;
        $singleton = new stdClass;
        $scoped = new stdClass;

        $registry->set(abstract: 'service', instance: $singleton);
        $this->assertSame(expected: $singleton, actual: $registry->get(abstract: 'service'));

        $registry->beginScope();
        $registry->setScoped(abstract: 'service', instance: $scoped);

        $this->assertTrue(condition: $registry->has(abstract: 'service'));
        $this->assertSame(expected: $scoped, actual: $registry->get(abstract: 'service'));

        $registry->endScope();
        $this->assertSame(expected: $singleton, actual: $registry->get(abstract: 'service'));
    }

    public function test_end_scope_without_active_scope_throws(): void
    {
        $registry = new ScopeRegistry;

        $this->expectException(exception: LogicException::class);

        $registry->endScope();
    }

    public function test_clear_resets_state(): void
    {
        $registry = new ScopeRegistry;
        $registry->set(abstract: 'service', instance: new stdClass);
        $registry->beginScope();
        $registry->setScoped(abstract: 'scoped', instance: new stdClass);

        $registry->clear();

        $this->assertFalse(condition: $registry->has(abstract: 'service'));
        $this->assertFalse(condition: $registry->has(abstract: 'scoped'));
    }
}

final class ScopeManagerTest extends TestCase
{
    public function test_terminate_delegates_to_registry(): void
    {
        $registry = new ScopeRegistry;
        $registry->set(abstract: 'service', instance: new stdClass);

        $manager = new ScopeManager(registry: $registry);
        $manager->terminate();

        $this->assertFalse(condition: $registry->has(abstract: 'service'));
    }
}
