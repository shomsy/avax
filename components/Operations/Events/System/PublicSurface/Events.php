<?php
declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\PublicSurface;

use Avax\Components\Operations\Events\System\Capabilities\Dispatcher\EventDispatcher;
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;

/**
 * Public surface for the Events component.
 */
final class Events implements EventsInterface
{
    private ListenerRegistry $registry;
    private EventDispatcher $dispatcher;

    public function __construct()
    {
        $this->registry = new ListenerRegistry();
        $this->dispatcher = new EventDispatcher($this->registry);
    }

    public function dispatch(string|object $event, mixed $data = null): void
    {
        $this->dispatcher->dispatch($event, $data);
    }

    public function listen(string $event, callable $listener, int $priority = 0): void
    {
        $this->registry->subscribe($event, $listener, $priority);
    }

    public function flush(): void
    {
        $this->registry->clear();
    }
}