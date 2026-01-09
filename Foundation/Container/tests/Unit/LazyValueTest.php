<?php

declare(strict_types=1);
namespace Avax\Tests\Container\Unit;

use Avax\Container\Features\Actions\Advanced\Lazy\LazyValue;
use PHPUnit\Framework\TestCase;

final class LazyValueTest extends TestCase
{
    public function testLazyValueInitializesOnce() : void
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