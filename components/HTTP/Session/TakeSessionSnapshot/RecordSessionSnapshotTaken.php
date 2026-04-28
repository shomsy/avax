<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\TakeSessionSnapshot;

final class RecordSessionSnapshotTaken
{
    private $audit;

    public function __construct($audit = null)
    {
        $this->audit = $audit;
    }

    public function handle(string $name) : void
    {
        $this->audit?->record('session.snapshot_taken', ['name' => $name]);
    }
}