<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Flows\Send;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;
use Avax\Components\Operations\Mail\System\Capabilities\Transport\MailTransport;
use Avax\Components\Operations\Mail\System\Capabilities\Transport\TransportResult;
use Avax\Components\Operations\Mail\System\PublicSurface\SendResult;

final class SendMail
{
    private MailTransport $transport;
    private array         $queue = [];

    public function __construct(MailTransport $transport)
    {
        $this->transport = $transport;
    }

    public function queue(MimeMessage $message, Envelope $envelope) : void
    {
        $this->queue[] = ['message' => $message, 'envelope' => $envelope];
    }

    public function flush() : array
    {
        $results = [];

        foreach ($this->queue as $item) {
            $results[] = $this->send($item['message'], $item['envelope']);
        }

        $this->queue = [];

        return $results;
    }

    public function send(MimeMessage $message, Envelope $envelope) : SendResult
    {
        $result = $this->transport->send($message, $envelope);

        if ($result->success) {
            return SendResult::success($result->messageId ?? '');
        }

        return SendResult::failure($result->error ?? 'Unknown error');
    }

    public function queuedCount() : int
    {
        return count($this->queue);
    }
}