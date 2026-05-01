<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Transport;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;

final readonly class NullTransport implements MailTransport
{
    public function send(MimeMessage $message, Envelope $envelope): TransportResult
    {
        return new TransportResult(
            success  : true,
            messageId: '<' . uniqid(prefix: 'msg-', more_entropy: true) . '-null@local>',
        );
    }

    public function supports(string $driver): bool
    {
        return $driver === 'null';
    }
}
