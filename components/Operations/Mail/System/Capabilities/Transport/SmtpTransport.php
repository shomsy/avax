<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Transport;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;

final readonly class SmtpTransport implements MailTransport
{
    public function __construct(private array $config = []) {}

    public function send(MimeMessage $mimeMessage, Envelope $envelope) : TransportResult
    {
        $headers = [
            'From: ' . $envelope->from,
            'MIME-Version: 1.0',
            'Content-Type: ' . $mimeMessage->contentType . '; charset=UTF-8',
        ];

        $sent = mail(
            to                : $mimeMessage->to,
            subject           : $mimeMessage->subject,
            message           : $mimeMessage->body,
            additional_headers: implode(separator: "\r\n", array: $headers),
        );

        if (! $sent) {
            return new TransportResult(success: false, error: 'PHP mail transport rejected the message.');
        }

        return new TransportResult(
            success  : true,
            messageId: '<' . uniqid(prefix: 'msg-', more_entropy: true) . '@' . ($this->config['domain'] ?? 'localhost') . '>',
        );
    }

    public function supports(string $driver): bool
    {
        return $driver === 'smtp';
    }
}
