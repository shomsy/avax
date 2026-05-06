<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Transport;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;

final class LogTransport implements MailTransport
{
    /** @var list<array<string, string>> */
    private array $messages = [];

    public function send(MimeMessage $mimeMessage, Envelope $envelope): TransportResult
    {
        $this->messages[] = [
            'from' => $envelope->from,
            'to' => $mimeMessage->to,
            'subject' => $mimeMessage->subject,
        ];

        return new TransportResult(
            success  : true,
            messageId: '<'.uniqid(prefix: 'msg-', more_entropy: true).'-logged@local>',
        );
    }

    public function supports(string $driver): bool
    {
        return $driver === 'log';
    }

    public function messages(): array
    {
        return $this->messages;
    }
}
