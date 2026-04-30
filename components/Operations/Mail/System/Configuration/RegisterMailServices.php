<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Configuration;

use Avax\Components\Operations\Mail\System\Capabilities\Transport\LogTransport;
use Avax\Components\Operations\Mail\System\Flows\Send\SendMail;
use Avax\Components\Operations\Mail\System\PublicSurface\Mailer;

/**
 * Configuration unit to assemble Mail component services.
 */
final class RegisterMailServices
{
    public function build() : Mailer
    {
        return new Mailer(
            sendMail: new SendMail(
                          transport: new LogTransport(),
                      ),
        );
    }
}
