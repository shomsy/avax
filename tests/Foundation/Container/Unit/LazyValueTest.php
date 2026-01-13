<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Features\Actions\Advanced\Lazy\LazyValue;
use PHPUnit\Framework\TestCase;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Unit/LazyValueTest.md#quick-summary
 */
final class LazyValueTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function test_lazy_value_initializes_once() : void
    {
        $calls = 0;
        $lazy  = new LazyValue(factory: static function () use (&$calls) : string {
            $calls++;

            return 'value';
        });

        $this->assertSame(expected: 'value', actual: $lazy->get());
        $this->assertSame(expected: 'value', actual: $lazy->get());
        $this->assertSame(expected: 1, actual: $calls);
    }
}
