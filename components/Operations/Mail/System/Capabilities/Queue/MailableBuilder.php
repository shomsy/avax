<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Queue;

final readonly class MailableBuilder
{
    private Mailable $mailable;

    public function __construct(string $to)
    {
        $this->mailable = new Mailable()->to(address: $to);
    }

    public function subject(string $subject): Mailable
    {
        return $this->mailable->subject(subject: $subject);
    }

    public function body(string $body): Mailable
    {
        return $this->mailable->body(body: $body);
    }

    public function send(): string
    {
        return MailQueue::send(mailable: $this->mailable);
    }

    public function sendNow(): bool
    {
        return MailQueue::sendNow(mailable: $this->mailable);
    }
}
