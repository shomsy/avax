<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\PublicSurface;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\CommandBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\QueryBus;

interface Command {}

interface Query {}

interface DomainEvent {}

final class MessageBus
{
    private static CommandBus $commandBus;
    private static QueryBus $queryBus;
    private static EventBus $eventBus;

    public static function query(object $query) : mixed
    {
        return self::queryBus()->dispatch($query);
    }

    public static function dispatch(object $command) : mixed
    {
        return self::commandBus()->dispatch($command);
    }

    private static function commandBus() : CommandBus
    {
        if (! isset(self::$commandBus)) {
            self::$commandBus = new CommandBus();
        }

        return self::$commandBus;
    }

    private static function queryBus() : QueryBus
    {
        if (! isset(self::$queryBus)) {
            self::$queryBus = new QueryBus();
        }

        return self::$queryBus;
    }

    public static function publish(object $event) : void
    {
        self::eventBus()->dispatch($event);
    }

    private static function eventBus() : EventBus
    {
        if (! isset(self::$eventBus)) {
            self::$eventBus = new EventBus();
        }

        return self::$eventBus;
    }

    public static function listen(string $messageClass, object $handler) : void
    {
        if (is_a($messageClass, Command::class, true)) {
            self::commandBus()->register($messageClass, $handler);
        } elseif (is_a($messageClass, Query::class, true)) {
            self::queryBus()->register($messageClass, $handler);
        } elseif (is_a($messageClass, DomainEvent::class, true)) {
            self::eventBus()->register($messageClass, $handler);
        }
    }
}
