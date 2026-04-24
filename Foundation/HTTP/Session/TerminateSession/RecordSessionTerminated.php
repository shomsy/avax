<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\TerminateSession;

final class RecordSessionTerminated
{
    private $audit;

    public function __construct($audit = null)
    {
        $this->audit = $audit;
    }

    public function handle(string $reason) : void
    {
        $this->audit?->record('session.terminated', ['reason' => $reason]);
    }
}