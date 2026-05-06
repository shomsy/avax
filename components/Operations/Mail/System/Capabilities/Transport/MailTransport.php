<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Transport;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;

interface MailTransport
{
    public function send(MimeMessage $mimeMessage, Envelope $envelope): TransportResult;

    public function supports(string $driver): bool;
}
