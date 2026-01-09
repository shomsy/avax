<?php

declare(strict_types=1);
namespace Avax\Tests\Container\Unit;

use Avax\Container\Features\Operate\Scope\ScopeManager;
use Avax\Container\Features\Operate\Scope\ScopeRegistry;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class ScopeRegistryTest extends TestCase
{
    public function testSetScopedWithoutActiveScopeThrows() : void
    {
        $manager = new ScopeRegistry();

        $this->expectException(exception: RuntimeException::class);

        $manager->setScoped(abstract: 'service', instance: new stdClass());
    }

    public function testBeginAndEndScopeControlsScopedInstances() : void
    {
        $manager   = new ScopeRegistry();
        $singleton = new stdClass();
        $scoped    = new stdClass();

        $manager->set(abstract: 'service', instance: $singleton);
        $this->assertSame(expected: $singleton, actual: $manager->get(abstract: 'service'));

        $manager->beginScope();
        $manager->setScoped(abstract: 'service', instance: $scoped);

        $this->assertTrue(condition: $manager->has(abstract: 'service'));
        $this->assertSame(expected: $scoped, actual: $manager->get(abstract: 'service'));

        $manager->endScope();
        $this->assertSame(expected: $singleton, actual: $manager->get(abstract: 'service'));
    }

    public function testEndScopeWithoutActiveScopeThrows() : void
    {
        $manager = new ScopeRegistry();

        $this->expectException(exception: LogicException::class);

        $manager->endScope();
    }

    public function testClearResetsState() : void
    {
        $manager = new ScopeRegistry();
        $manager->set(abstract: 'service', instance: new stdClass());
        $manager->beginScope();
        $manager->setScoped(abstract: 'scoped', instance: new stdClass());

        $manager->clear();

        $this->assertFalse(condition: $manager->has(abstract: 'service'));
        $this->assertFalse(condition: $manager->has(abstract: 'scoped'));
    }
}

final class ScopeManagerTest extends TestCase
{
    public function testTerminateDelegatesToRegistry() : void
    {
        $registry = new ScopeRegistry();
        $registry->set(abstract: 'service', instance: new stdClass());

        $manager = new ScopeManager(registry: $registry);
        $manager->terminate();

        $this->assertFalse(condition: $registry->has(abstract: 'service'));
    }
}