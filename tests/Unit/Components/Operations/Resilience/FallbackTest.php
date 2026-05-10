<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\Fallback\Fallback;
use Avax\Components\Operations\Resilience\System\Capabilities\Fallback\FallbackBuilder;
use Avax\Components\Operations\Resilience\System\Capabilities\Fallback\FallbackStrategyItem;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;

final class FallbackTest extends TestCase
{
    #[Test]
    public function it_registers_fallback_strategy() : void
    {
        $item = new FallbackStrategyItem(
            dependency   : 'ExternalApi',
            fallbackClass: 'MockFallback',
        );

        Fallback::register('ExternalApi', $item);

        self::assertFalse(Fallback::isDegraded('ExternalApi'));

        Fallback::markDegraded('ExternalApi');

        self::assertTrue(Fallback::isDegraded('ExternalApi'));
    }

    #[Test]
    public function it_reports_not_degraded_by_default() : void
    {
        self::assertFalse(Fallback::isDegraded('UnknownService'));
    }

    #[Test]
    public function it_marks_dependency_as_degraded() : void
    {
        $item = new FallbackStrategyItem(
            dependency   : 'PaymentGateway',
            fallbackClass: 'FallbackPayment',
        );

        Fallback::register('PaymentGateway', $item);

        self::assertFalse(Fallback::isDegraded('PaymentGateway'));

        Fallback::markDegraded('PaymentGateway');

        self::assertTrue(Fallback::isDegraded('PaymentGateway'));
    }

    #[Test]
    public function it_recovers_dependency_from_degraded_state() : void
    {
        $item = new FallbackStrategyItem(
            dependency   : 'CacheService',
            fallbackClass: 'FallbackCache',
        );

        Fallback::register('CacheService', $item);
        Fallback::markDegraded('CacheService');
        self::assertTrue(Fallback::isDegraded('CacheService'));

        Fallback::recover('CacheService');

        self::assertFalse(Fallback::isDegraded('CacheService'));
    }

    #[Test]
    public function it_executes_successful_fallback() : void
    {
        $result = Fallback::execute([
                                        static fn () : string => 'primary',
                                    ]);

        self::assertSame('primary', $result);
    }

    #[Test]
    public function it_executes_secondary_fallback_when_primary_fails() : void
    {
        $result = Fallback::execute([
                                        static fn () : never => throw new RuntimeException('primary failed'),
                                        static fn () : string => 'secondary',
                                    ]);

        self::assertSame('secondary', $result);
    }

    #[Test]
    public function it_tries_all_fallbacks_until_one_succeeds() : void
    {
        $result = Fallback::execute([
                                        static fn () : never => throw new RuntimeException('first'),
                                        static fn () : never => throw new RuntimeException('second'),
                                        static fn () : string => 'third',
                                    ]);

        self::assertSame('third', $result);
    }

    #[Test]
    public function it_throws_when_all_fallbacks_fail() : void
    {
        try {
            Fallback::execute([
                                  static fn () : never => throw new RuntimeException('fallback 1'),
                                  static fn () : never => throw new RuntimeException('fallback 2'),
                                  static fn () : never => throw new RuntimeException('fallback 3'),
                              ]);
            self::fail('Expected exception was not thrown');
        } catch (RuntimeException $e) {
            self::assertSame('fallback 3', $e->getMessage());
        }
    }

    #[Test]
    public function it_throws_last_exception_when_all_fallbacks_fail() : void
    {
        try {
            Fallback::execute([
                                  static fn () : never => throw new RuntimeException('first error'),
                                  static fn () : never => throw new RuntimeException('last error'),
                              ]);
        } catch (RuntimeException $e) {
            self::assertSame('last error', $e->getMessage());

            return;
        }

        self::fail('Expected exception was not thrown');
    }

    #[Test]
    public function it_creates_fallback_builder_for_dependency() : void
    {
        $builder = Fallback::for('TestDependency');

        self::assertInstanceOf(FallbackBuilder::class, $builder);
    }

    #[Test]
    public function it_registers_strategy_via_builder() : void
    {
        $builder = Fallback::for('BuilderTest');
        $builder->use('MockFallbackClass');

        self::assertFalse(Fallback::isDegraded('BuilderTest'));

        Fallback::markDegraded('BuilderTest');

        self::assertTrue(Fallback::isDegraded('BuilderTest'));
    }

    #[Test]
    public function fallback_builder_when_returns_self() : void
    {
        $builder     = Fallback::for('ChainedDependency');
        $chainResult = $builder->when();

        self::assertInstanceOf(FallbackBuilder::class, $chainResult);
    }

    #[Test]
    public function it_handles_multiple_independent_dependencies() : void
    {
        $itemA = new FallbackStrategyItem(dependency: 'ServiceA', fallbackClass: 'FallbackA');
        $itemB = new FallbackStrategyItem(dependency: 'ServiceB', fallbackClass: 'FallbackB');

        Fallback::register('ServiceA', $itemA);
        Fallback::register('ServiceB', $itemB);

        Fallback::markDegraded('ServiceA');

        self::assertTrue(Fallback::isDegraded('ServiceA'));
        self::assertFalse(Fallback::isDegraded('ServiceB'));

        Fallback::markDegraded('ServiceB');

        self::assertTrue(Fallback::isDegraded('ServiceB'));

        Fallback::recover('ServiceA');

        self::assertFalse(Fallback::isDegraded('ServiceA'));
        self::assertTrue(Fallback::isDegraded('ServiceB'));
    }

    #[Test]
    public function fallback_strategy_item_is_active_by_default_false() : void
    {
        $item = new FallbackStrategyItem(
            dependency   : 'TestDep',
            fallbackClass: 'TestClass',
        );

        self::assertFalse($item->isActive());
    }

    #[Test]
    public function fallback_strategy_item_can_be_activated() : void
    {
        $item = new FallbackStrategyItem(
            dependency   : 'TestDep',
            fallbackClass: 'TestClass',
        );

        $item->activate();

        self::assertTrue($item->isActive());
    }

    #[Test]
    public function fallback_strategy_item_can_be_deactivated() : void
    {
        $item = new FallbackStrategyItem(
            dependency   : 'TestDep',
            fallbackClass: 'TestClass',
            active       : true,
        );

        $item->deactivate();

        self::assertFalse($item->isActive());
    }

    #[Test]
    public function fallback_strategy_item_stores_dependency_and_class() : void
    {
        $item = new FallbackStrategyItem(
            dependency   : 'MyService',
            fallbackClass: 'MyFallback',
        );

        self::assertSame('MyService', $item->dependency);
        self::assertSame('MyFallback', $item->fallbackClass);
    }

    #[Test]
    public function it_executes_fallback_returning_null() : void
    {
        $result = Fallback::execute([
                                        static fn () : ?string => null,
                                    ]);

        self::assertNull($result);
    }

    #[Test]
    public function it_executes_fallback_returning_array() : void
    {
        $result = Fallback::execute([
                                        static fn () : never => throw new RuntimeException('fail'),
                                        static fn () : array => ['fallback' => 'data'],
                                    ]);

        self::assertSame(['fallback' => 'data'], $result);
    }

    #[Test]
    public function it_executes_fallback_returning_object() : void
    {
        $expectedObject     = new stdClass();
        $expectedObject->id = 99;

        $result = Fallback::execute([
                                        static fn () : never => throw new RuntimeException('fail'),
                                        static fn () => $expectedObject,
                                    ]);

        self::assertSame($expectedObject, $result);
    }

    #[Test]
    public function it_executes_single_fallback_that_fails() : void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('single failure');

        Fallback::execute([
                              static fn () : never => throw new RuntimeException('single failure'),
                          ]);
    }

    #[Test]
    public function it_does_not_call_subsequent_fallbacks_after_success() : void
    {
        $callCount = 0;

        Fallback::execute([
                              static function () use (&$callCount) : string {
                                  $callCount++;

                                  return 'immediate';
                              },
                              static function () use (&$callCount) : never {
                                  $callCount++;
                                  throw new RuntimeException('should not be called');
                              },
                          ]);

        self::assertSame(1, $callCount);
    }

    #[Test]
    public function it_handles_different_exception_types_in_fallbacks() : void
    {
        $result = Fallback::execute([
                                        static fn () : never => throw new InvalidArgumentException('invalid'),
                                        static fn () : never => throw new RuntimeException('runtime'),
                                        static fn () : string => 'recovered',
                                    ]);

        self::assertSame('recovered', $result);
    }

    #[Test]
    public function recovering_nonexistent_dependency_does_not_error() : void
    {
        Fallback::recover('NonExistent');

        self::assertFalse(Fallback::isDegraded('NonExistent'));
    }

    #[Test]
    public function marking_nonexistent_dependency_degraded_does_not_error() : void
    {
        Fallback::markDegraded('NonExistent');

        self::assertFalse(Fallback::isDegraded('NonExistent'));
    }

    protected function tearDown() : void
    {
        $reflection = new ReflectionClass(Fallback::class);
        $property   = $reflection->getProperty('strategies');
        $property->setValue(null, []);
    }
}
