<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RestoreSessionSnapshot;

final class PublishSessionSnapshotRestored
{
    private $events;

    public function __construct($events = null)
    {
        $this->events = $events;
    }

    public function handle(string $name) : void
    {
        $this->events?->dispatch('session.snapshot_restored', ['name' => $name]);
    }
}