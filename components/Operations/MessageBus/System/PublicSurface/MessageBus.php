<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\PublicSurface;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\CommandBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\QueryBus;

final class MessageBus
{
    private static ?self $instance = null;

    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus   $queryBus,
        private readonly EventBus   $eventBus,
    )
    {
        self::$instance = $this;
    }

    public static function query(object $query): mixed
    {
        return self::instance()->queryBus->dispatch($query);
    }

    public static function dispatch(object $command): mixed
    {
        return self::instance()->commandBus->dispatch($command);
    }

    public static function publish(object $event): void
    {
        self::instance()->eventBus->dispatch($event);
    }

    public static function listen(string $messageClass, object $handler): void
    {
        $instance = self::instance();

        if (is_a($messageClass, Command::class, true)) {
            $instance->commandBus->register($messageClass, $handler);
        } elseif (is_a($messageClass, Query::class, true)) {
            $instance->queryBus->register($messageClass, $handler);
        } elseif (is_a($messageClass, DomainEvent::class, true)) {
            $instance->eventBus->register($messageClass, $handler);
        }
    }

    private static function instance() : self
    {
        if (self::$instance === null) {
            // Fallback for non-booted environments (e.g. tests without full container)
            self::$instance = new self(
                commandBus: new CommandBus(),
                queryBus  : new QueryBus(),
                eventBus  : new EventBus(),
            );
        }

        return self::$instance;
    }
}
