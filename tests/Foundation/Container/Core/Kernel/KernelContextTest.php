<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Core\Kernel;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @see docs/tests/Kernel/KernelContextTest.md#quick-summary
 */
final class KernelContextTest extends TestCase
{
    public function test_child_context_inherits_parent_depth() : void
    {
        $root  = new KernelContext(serviceId: 'root', depth: 1, debug: true, allowAutowire: false, manualInjection: true, traceId: 't1', overrides: ['a' => 1]);
        $child = $root->child(serviceId: 'child', overrides: ['b' => 2]);

        $this->assertSame(expected: 2, actual: $child->depth);
        $this->assertSame(expected: $root, actual: $child->parent);
        $this->assertSame(expected: 'root', actual: $child->consumer);
        $this->assertFalse(condition: $child->allowAutowire);
        $this->assertTrue(condition: $child->manualInjection);
        $this->assertSame(expected: 't1', actual: $child->traceId);
        $this->assertSame(expected: ['b' => 2], actual: $child->overrides);
    }

    public function test_cycle_detection() : void
    {
        $a = new KernelContext(serviceId: 'A');
        $b = $a->child(serviceId: 'B');
        $c = $b->child(serviceId: 'C');

        $this->assertTrue(condition: $c->contains(serviceId: 'A'));
        $this->assertTrue(condition: $c->contains(serviceId: 'B'));
        $this->assertFalse(condition: $c->contains(serviceId: 'Z'));
    }

    public function test_set_meta_once_throws_on_conflicting_value() : void
    {
        $ctx = new KernelContext(serviceId: 'S');
        $ctx->setMetaOnce(namespace: 'n', key: 'k', value: 1);

        $this->expectException(exception: LogicException::class);
        $ctx->setMetaOnce(namespace: 'n', key: 'k', value: 2);
    }

    public function test_resolved_with_throws_on_double_call() : void
    {
        $ctx = new KernelContext(serviceId: 'S');
        $ctx->resolvedWith(instance: new stdClass);

        $this->expectException(exception: LogicException::class);
        $ctx->resolvedWith(instance: new stdClass);
    }

    public function test_overwrite_with_merges_metadata_and_overrides_instance() : void
    {
        $ctx = new KernelContext(serviceId: 'S', metadata: ['a' => 1]);
        $ctx->resolvedWith(instance: new stdClass);

        $replacement = new stdClass;
        $ctx->overwriteWith(instance: $replacement);

        $this->assertSame(expected: $replacement, actual: $ctx->getInstance());
        $this->assertSame(expected: ['a' => 1], actual: $ctx->metadata);
    }
}
