<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Flows\Queue;

use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;
use Avax\Components\Operations\Mail\System\PublicSurface\Mailer;

final readonly class QueueMail
{
    public function __construct(private Mailer $mailer) {}

    public function queue(MimeMessage $message): void
    {
        $this->mailer->queue(message: $message);
    }
}
