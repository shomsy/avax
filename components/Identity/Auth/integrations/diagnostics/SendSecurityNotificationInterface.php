<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Diagnostics;

interface SendSecurityNotificationInterface
{
    public function send(SecurityNotification $notification) : void;
}
