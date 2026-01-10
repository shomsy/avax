<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Features\Actions\Advanced\Lazy\LazyValue;
use Avax\Container\Features\Core\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Unit/LazyBindingTest.md#quick-summary
 */
final class LazyBindingTest extends TestCase
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Throwable
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function testBindingCanReturnLazyValue(): void
    {
        $calls     = 0;
        $lazyValue = new LazyValue(factory: static function () use (&$calls): object {
            $calls++;

            return new stdClass();
        });

        $builder = ContainerBuilder::create();
        $builder->bind(abstract: 'counter', concrete: $lazyValue);

        $container = $builder->build();

        $lazy = $container->get(id: 'counter');

        $this->assertSame(expected: $lazyValue, actual: $lazy);
        $this->assertSame(expected: 0, actual: $calls);

        $lazy->get();
        $lazy->get();

        $this->assertSame(expected: 1, actual: $calls);
    }
}
