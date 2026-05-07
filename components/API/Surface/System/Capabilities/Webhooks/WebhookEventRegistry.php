<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\Webhooks;

final class WebhookEventRegistry
{
    /**
     * @var array<string, string>
     */
    private array $events = [];

    public function register(string $event, string $description) : self
    {
        $this->events[$event] = $description;

        return $this;
    }

    public function unregister(string $event) : self
    {
        unset($this->events[$event]);

        return $this;
    }

    public function exists(string $event) : bool
    {
        return isset($this->events[$event]);
    }

    public function description(string $event) : ?string
    {
        return $this->events[$event] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function all() : array
    {
        return $this->events;
    }
}
