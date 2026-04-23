<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RestoreSessionSnapshot;

final class RecordSessionSnapshotRestored
{
    private $audit;

    public function __construct($audit = null)
    {
        $this->audit = $audit;
    }

    public function handle(string $name) : void
    {
        $this->audit?->record('session.snapshot_restored', ['name' => $name]);
    }
}