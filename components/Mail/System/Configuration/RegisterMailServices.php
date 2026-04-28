<?php

declare(strict_types=1);

namespace Avax\Components\Mail\System\Configuration;

use Avax\Components\Mail\System\Capabilities\Transport\LogTransport;
use Avax\Components\Mail\System\Flows\Send\SendMail;
use Avax\Components\Mail\System\PublicSurface\Mailer;

/**
 * Configuration unit to assemble Mail component services.
 */
final class RegisterMailServices
{
    public function build() : Mailer
    {
        return new Mailer(
            sendMail: new SendMail(
                          transport: new LogTransport()
                      )
        );
    }
}