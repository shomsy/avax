<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Flows\Send;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;
use Avax\Components\Operations\Mail\System\Capabilities\Transport\MailTransport;
use Avax\Components\Operations\Mail\System\PublicSurface\SendResult;

final class SendMail
{
    private array $queue = [];

    public function __construct(private readonly MailTransport $mailTransport)
    {
    }

    public function queue(MimeMessage $mimeMessage, Envelope $envelope): void
    {
        $this->queue[] = ['message' => $mimeMessage, 'envelope' => $envelope];
    }

    public function flush(): array
    {
        $results = [];

        foreach ($this->queue as $item) {
            $results[] = $this->send($item['message'], $item['envelope']);
        }

        $this->queue = [];

        return $results;
    }

    public function send(MimeMessage $mimeMessage, Envelope $envelope): SendResult
    {
        $transportResult = $this->mailTransport->send($mimeMessage, $envelope);

        if ($transportResult->success) {
            return SendResult::success($transportResult->messageId ?? '');
        }

        return SendResult::failure($transportResult->error ?? 'Unknown error');
    }

    public function queuedCount(): int
    {
        return count($this->queue);
    }
}
