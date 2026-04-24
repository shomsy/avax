<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionAudit;

final class RecordSessionAudit
{
    private $audit;

    public function __construct($audit)
    {
        $this->audit = $audit;
    }

    public function handle(string $event, array $data = []) : void
    {
        $this->audit?->record($event, $data);
    }
}