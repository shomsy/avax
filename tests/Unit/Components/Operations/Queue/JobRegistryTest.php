<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JobRegistryTest extends TestCase
{
    private JobRegistry $registry;

    #[Test]
    public function it_registers_and_resolves_handler() : void
    {
        $handler = static fn (array $payload) : string => 'handled: ' . $payload['data'];

        $this->registry->register('ProcessData', $handler);

        $resolved = $this->registry->resolve('ProcessData');

        self::assertSame('handled: test', $resolved(['data' => 'test']));
    }

    #[Test]
    public function it_reports_handler_exists() : void
    {
        $handler = static fn () : int => 0;

        self::assertFalse($this->registry->has('NewHandler'));

        $this->registry->register('NewHandler', $handler);

        self::assertTrue($this->registry->has('NewHandler'));
    }

    #[Test]
    public function it_reports_handler_does_not_exist() : void
    {
        self::assertFalse($this->registry->has('MissingHandler'));
    }

    #[Test]
    public function it_throws_when_resolving_unregistered_handler() : void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('No handler registered for job: MissingHandler');

        $this->registry->resolve('MissingHandler');
    }

    #[Test]
    public function it_allows_overwriting_existing_handler() : void
    {
        $firstHandler  = static fn () : string => 'first';
        $secondHandler = static fn () : string => 'second';

        $this->registry->register('Overwritable', $firstHandler);
        $this->registry->register('Overwritable', $secondHandler);

        $resolved = $this->registry->resolve('Overwritable');

        self::assertSame('second', $resolved());
    }

    #[Test]
    public function it_supports_multiple_handlers() : void
    {
        $handlerA = static fn () : string => 'A';
        $handlerB = static fn () : string => 'B';
        $handlerC = static fn () : string => 'C';

        $this->registry->register('JobA', $handlerA);
        $this->registry->register('JobB', $handlerB);
        $this->registry->register('JobC', $handlerC);

        self::assertTrue($this->registry->has('JobA'));
        self::assertTrue($this->registry->has('JobB'));
        self::assertTrue($this->registry->has('JobC'));
        self::assertFalse($this->registry->has('JobD'));

        self::assertSame('A', $this->registry->resolve('JobA')());
        self::assertSame('B', $this->registry->resolve('JobB')());
        self::assertSame('C', $this->registry->resolve('JobC')());
    }

    #[Test]
    public function it_accepts_invokable_object_as_handler() : void
    {
        $invokable = new class {
            public function __invoke(array $payload) : int
            {
                return ($payload['a'] ?? 0) + ($payload['b'] ?? 0);
            }
        };

        $this->registry->register('AddNumbers', $invokable);

        $result = $this->registry->resolve('AddNumbers')(['a' => 5, 'b' => 3]);

        self::assertSame(8, $result);
    }

    #[Test]
    public function it_accepts_array_callable_as_handler() : void
    {
        $service = new class {
            public function handle(array $payload) : string
            {
                return 'service: ' . ($payload['name'] ?? 'unknown');
            }
        };

        $this->registry->register('ServiceHandler', [$service, 'handle']);

        $result = $this->registry->resolve('ServiceHandler')(['name' => 'test']);

        self::assertSame('service: test', $result);
    }

    #[Test]
    public function it_registers_handler_with_empty_string_name() : void
    {
        $handler = static fn () : string => 'empty';

        $this->registry->register('', $handler);

        self::assertTrue($this->registry->has(''));
        self::assertSame('empty', $this->registry->resolve('')());
    }

    #[Test]
    public function it_handles_handler_returning_null() : void
    {
        $handler = static fn () : ?string => null;

        $this->registry->register('NullHandler', $handler);

        $result = $this->registry->resolve('NullHandler')();

        self::assertNull($result);
    }

    #[Test]
    public function it_handles_handler_throwing_exception() : void
    {
        $handler = static fn () : never => throw new RuntimeException('Handler error');

        $this->registry->register('FailingHandler', $handler);

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Handler error');

        $this->registry->resolve('FailingHandler')();
    }

    #[Test]
    public function it_independently_tracks_different_handlers() : void
    {
        $this->registry->register('HandlerA', static fn () : string => 'A');
        $this->registry->register('HandlerB', static fn () : string => 'B');

        self::assertTrue($this->registry->has('HandlerA'));
        self::assertTrue($this->registry->has('HandlerB'));

        $this->registry->register('HandlerA', static fn () : string => 'A-overwritten');

        self::assertSame('A-overwritten', $this->registry->resolve('HandlerA')());
        self::assertSame('B', $this->registry->resolve('HandlerB')());
    }

    protected function setUp() : void
    {
        $this->registry = new JobRegistry();
    }
}
