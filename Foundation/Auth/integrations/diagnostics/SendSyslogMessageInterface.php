<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

interface SendSyslogMessageInterface
{
    public function send(string $severity, string $message) : void;
}
