<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\DeleteSessionValue;

final class RecordSessionValueDeleted
{
    private $audit;

    public function __construct($audit = null)
    {
        $this->audit = $audit;
    }

    public function handle(string $key) : void
    {
        $this->audit?->record('session.value_deleted', ['key' => $key]);
    }
}