<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\BeginSessionTransaction;

final class RecordSessionTransactionRolledBack
{
    private $audit;

    public function __construct($audit = null)
    {
        $this->audit = $audit;
    }

    public function handle() : void
    {
        $this->audit?->record('session.transaction_rolled_back');
    }
}