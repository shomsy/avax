<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\MessageBus\System\Capabilities\Bus;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\CommandBus;
use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CommandBusTest extends TestCase
{
    #[Test]
    public function it_dispatches_command_to_registered_handler() : void
    {
        $bus           = new CommandBus();
        $command       = new class implements Command {};
        $handlerCalled = false;

        $bus->register($command::class, static function (Command $cmd) use ($command, &$handlerCalled) : string {
            $handlerCalled = true;
            self::assertSame($command, $cmd);

            return 'handled';
        });

        $result = $bus->dispatch($command);

        self::assertTrue($handlerCalled);
        self::assertSame('handled', $result);
    }

    #[Test]
    public function it_returns_handler_result() : void
    {
        $bus     = new CommandBus();
        $command = new class implements Command {
            public string $id = 'cmd-1';
        };

        $bus->register($command::class, static fn (Command $cmd) : array => [
            'status' => 'ok',
            'id'     => $cmd->id,
        ]);

        $result = $bus->dispatch($command);

        self::assertSame(['status' => 'ok', 'id' => 'cmd-1'], $result);
    }

    #[Test]
    public function it_throws_when_no_handler_registered() : void
    {
        $bus     = new CommandBus();
        $command = new class implements Command {};

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No handler registered for command: ');

        $bus->dispatch($command);
    }

    #[Test]
    public function it_replaces_handler_when_registered_twice_for_same_command() : void
    {
        $bus     = new CommandBus();
        $command = new class implements Command {};

        $bus->register($command::class, static fn () : string => 'first');
        $bus->register($command::class, static fn () : string => 'second');

        $result = $bus->dispatch($command);

        self::assertSame('second', $result);
    }

    #[Test]
    public function it_executes_middleware_in_registration_order() : void
    {
        $bus            = new CommandBus();
        $command        = new class implements Command {};
        $executionOrder = [];

        $middleware1 = new class($executionOrder) {
            public function __construct(private array &$order) {}

            public function process(Command $command, callable $next) : mixed
            {
                $this->order[] = 'middleware1_before';
                $result        = $next($command);
                $this->order[] = 'middleware1_after';

                return $result;
            }
        };

        $middleware2 = new class($executionOrder) {
            public function __construct(private array &$order) {}

            public function process(Command $command, callable $next) : mixed
            {
                $this->order[] = 'middleware2_before';
                $result        = $next($command);
                $this->order[] = 'middleware2_after';

                return $result;
            }
        };

        $bus->use($middleware1);
        $bus->use($middleware2);

        $bus->register($command::class, static function (Command $cmd) use (&$executionOrder) : string {
            $executionOrder[] = 'handler';

            return 'done';
        });

        $result = $bus->dispatch($command);

        self::assertSame('done', $result);
        self::assertSame([
                             'middleware1_before',
                             'middleware2_before',
                             'handler',
                             'middleware2_after',
                             'middleware1_after',
                         ], $executionOrder);
    }

    #[Test]
    public function it_allows_middleware_to_short_circuit() : void
    {
        $bus           = new CommandBus();
        $command       = new class implements Command {};
        $handlerCalled = false;

        $shortCircuitMiddleware = new class {
            public function process(Command $command, callable $next) : mixed
            {
                return 'short-circuited';
            }
        };

        $bus->use($shortCircuitMiddleware);

        $bus->register($command::class, static function (Command $cmd) use (&$handlerCalled) : string {
            $handlerCalled = true;

            return 'handled';
        });

        $result = $bus->dispatch($command);

        self::assertSame('short-circuited', $result);
        self::assertFalse($handlerCalled);
    }

    #[Test]
    public function it_allows_middleware_to_mutate_command() : void
    {
        $bus     = new CommandBus();
        $command = new class implements Command {
            public string $value = 'original';
        };

        $mutationMiddleware = new class {
            public function process(Command $command, callable $next) : mixed
            {
                $command->value = 'mutated';

                return $next($command);
            }
        };

        $bus->use($mutationMiddleware);

        $bus->register($command::class, static fn (Command $cmd) : string => $cmd->value);

        $result = $bus->dispatch($command);

        self::assertSame('mutated', $result);
    }

    #[Test]
    public function it_handles_multiple_commands_independently() : void
    {
        $bus      = new CommandBus();
        $commandA = new class implements Command {};
        $commandB = new class implements Command {};

        $bus->register($commandA::class, static fn () : string => 'A');
        $bus->register($commandB::class, static fn () : string => 'B');

        self::assertSame('A', $bus->dispatch($commandA));
        self::assertSame('B', $bus->dispatch($commandB));
    }

    #[Test]
    public function it_distinguishes_commands_by_class_name_not_instance() : void
    {
        $bus      = new CommandBus();
        $command1 = new class implements Command {};
        $command2 = new class implements Command {};

        $bus->register($command1::class, static fn () : string => 'first');
        $bus->register($command2::class, static fn () : string => 'second');

        self::assertSame('first', $bus->dispatch($command1));
        self::assertSame('second', $bus->dispatch($command2));
    }

    #[Test]
    public function it_handles_command_with_no_middleware() : void
    {
        $bus     = new CommandBus();
        $command = new class implements Command {
            public int $x = 5;
            public int $y = 3;
        };

        $bus->register($command::class, static fn (Command $cmd) : int => $cmd->x + $cmd->y);

        $result = $bus->dispatch($command);

        self::assertSame(8, $result);
    }

    #[Test]
    public function it_supports_multiple_middleware_layers() : void
    {
        $bus     = new CommandBus();
        $command = new class implements Command {};
        $calls   = [];

        for ($i = 1; $i <= 5; $i++) {
            $middleware = new class($i, $calls) {
                public function __construct(private int $id, private array &$calls) {}

                public function process(Command $command, callable $next) : mixed
                {
                    $this->calls[] = "before_{$this->id}";
                    $result        = $next($command);
                    $this->calls[] = "after_{$this->id}";

                    return $result;
                }
            };
            $bus->use($middleware);
        }

        $bus->register($command::class, static function () use (&$calls) : string {
            $calls[] = 'handler';

            return 'ok';
        });

        $bus->dispatch($command);

        self::assertSame([
                             'before_1', 'before_2', 'before_3', 'before_4', 'before_5',
                             'handler',
                             'after_5', 'after_4', 'after_3', 'after_2', 'after_1',
                         ], $calls);
    }

    #[Test]
    public function it_propagates_exception_from_handler() : void
    {
        $bus     = new CommandBus();
        $command = new class implements Command {};

        $bus->register($command::class, static function () : never {
            throw new RuntimeException('handler failure');
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('handler failure');

        $bus->dispatch($command);
    }

    #[Test]
    public function it_propagates_exception_from_middleware() : void
    {
        $bus     = new CommandBus();
        $command = new class implements Command {};

        $failingMiddleware = new class {
            public function process(Command $command, callable $next) : mixed
            {
                throw new RuntimeException('middleware failure');
            }
        };

        $bus->use($failingMiddleware);
        $bus->register($command::class, static fn () : string => 'handled');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('middleware failure');

        $bus->dispatch($command);
    }
}
