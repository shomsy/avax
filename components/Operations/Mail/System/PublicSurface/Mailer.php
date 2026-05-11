<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\PublicSurface;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;
use Avax\Components\Operations\Mail\System\Flows\Send\SendMail;

final readonly class Mailer
{
    public function __construct(
        private SendMail $sendMail,
        private Envelope $envelope = new Envelope(from: 'noreply@localhost'),
    ) {
    }

    public function send(MimeMessage $mimeMessage): SendResult
    {
        return $this->sendMail->send(envelope: $this->envelope, message: $mimeMessage);
    }

    public function queue(MimeMessage $mimeMessage): void
    {
        $this->sendMail->queue(envelope: $this->envelope, message: $mimeMessage);
    }

    public function raw(): RawMailBuilder
    {
        return new RawMailBuilder(envelope: $this->envelope, sender: $this->sendMail);
    }
}

final readonly class SendResult
{
    public function __construct(
        public bool $success,
        public string|null $messageId = null,
        public string|null $error = null,
    ) {
    }

    public static function success(string $messageId): self
    {
        return new self(success: true, messageId: $messageId);
    }

    public static function failure(string $error): self
    {
        return new self(success: false, error: $error);
    }
}

final class RawMailBuilder
{
    private string|null $from = null;

    private string|null $to = null;

    private string|null $subject = null;

    private string|null $body = null;

    /** @var array<string, string> */
    private array $headers = [];

    public function __construct(
        private readonly SendMail $sendMail,
        private readonly Envelope $envelope,
    ) {
    }

    public function from(string $address, string|null $name = null) : self
    {
        $this->from = $name !== null ? sprintf('%s <%s>', $name, $address) : $address;

        return $this;
    }

    public function to(string $address, string|null $name = null) : self
    {
        $this->to = $name !== null ? sprintf('%s <%s>', $name, $address) : $address;

        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function send(): SendResult
    {
        if ($this->from === null || $this->to === null || $this->subject === null || $this->body === null) {
            return SendResult::failure(error: 'Missing required fields: from, to, subject, body');
        }

        return $this->sendMail->send(
            envelope: $this->envelope->withFrom(from: $this->from),
            message : new MimeMessage(
                from   : $this->from,
                to     : $this->to,
                subject: $this->subject,
                body   : $this->body,
                headers: $this->headers,
            ),
        );
    }
}
