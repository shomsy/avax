<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\ClearSession;

final class RecordSessionCleared
{
    private $audit;

    public function __construct($audit = null)
    {
        $this->audit = $audit;
    }

    public function handle() : void
    {
        $this->audit?->record('session.cleared');
    }
}