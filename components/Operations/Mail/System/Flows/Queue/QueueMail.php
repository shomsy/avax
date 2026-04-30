<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Flows\Queue;

use Avax\Components\Operations\Mail\System\PublicSurface\Mailer;

final class QueueMail
{
    public static function execute(MailMessage $message): void
    {
        Mailer::send($message);
    }
}
