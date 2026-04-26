<?php

declare(strict_types=1);

namespace components\HTTP\Session\ExportSessionState;

final class RecordSessionStateExported
{
    private $audit;

    public function __construct($audit = null)
    {
        $this->audit = $audit;
    }

    public function handle() : void
    {
        $this->audit?->record('session.state_exported');
    }
}