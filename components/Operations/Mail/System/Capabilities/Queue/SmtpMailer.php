<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Queue;

final readonly class SmtpMailer
{
    public function __construct(private array $config = []) {}

    public function send(Mailable $mailable): bool
    {
        if (($this->config['driver'] ?? 'mail') === 'array') {
            return true;
        }

        $headers = [
            'From: '.($mailable->getFrom() ?? $this->config['from'] ?? 'noreply@localhost'),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];

        foreach ($mailable->getCc() as $cc) {
            $headers[] = 'Cc: '.$cc;
        }

        return mail(
            to                : $mailable->getTo(),
            subject           : $mailable->getSubject(),
            message           : $mailable->getBody(),
            additional_headers: implode(separator: "\r\n", array: $headers),
        );
    }
}
