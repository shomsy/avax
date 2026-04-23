<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\WriteSessionValue;

final class RecordSessionValueStored
{
    private $audit;

    public function __construct($audit = null)
    {
        $this->audit = $audit;
    }

    public function handle(string $key, mixed $value) : void
    {
        $this->audit?->record('session.value_stored', ['key' => $key]);
    }
}