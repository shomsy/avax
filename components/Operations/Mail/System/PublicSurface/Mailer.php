<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\PublicSurface;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;
use Avax\Components\Operations\Mail\System\Flows\Send\SendMail;

final class Mailer
{
    private SendMail $sender;
    private Envelope $envelope;

    public function __construct(SendMail $sender, Envelope $envelope)
    {
        $this->sender   = $sender;
        $this->envelope = $envelope;
    }

    public function send(MimeMessage $message) : SendResult
    {
        return $this->sender->send($message, $this->envelope);
    }

    public function queue(MimeMessage $message) : void
    {
        $this->sender->queue($message, $this->envelope);
    }

    public function raw() : RawMailBuilder
    {
        return new RawMailBuilder($this->sender, $this->envelope);
    }
}

final class SendResult
{
    public function __construct(
        public readonly bool        $success,
        public readonly string|null $messageId = null,
        public readonly string|null $error = null,
    ) {}

    public static function success(string $messageId) : self
    {
        return new self(success: true, messageId: $messageId);
    }

    public static function failure(string $error) : self
    {
        return new self(success: false, error: $error);
    }
}

final class RawMailBuilder
{
    private SendMail $sender;
    private Envelope $envelope;
    private string|null $from    = null;
    private string|null $to      = null;
    private string|null $subject = null;
    private string|null $body    = null;
    private array    $headers = [];

    public function __construct(SendMail $sender, Envelope $envelope)
    {
        $this->sender   = $sender;
        $this->envelope = $envelope;
    }

    public function from(string $address, string|null $name = null) : self
    {
        $this->from = $name !== null ? "$name <$address>" : $address;

        return $this;
    }

    public function to(string $address, string|null $name = null) : self
    {
        $this->to = $name !== null ? "$name <$address>" : $address;

        return $this;
    }

    public function subject(string $subject) : self
    {
        $this->subject = $subject;

        return $this;
    }

    public function body(string $body) : self
    {
        $this->body = $body;

        return $this;
    }

    public function header(string $name, string $value) : self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function send() : SendResult
    {
        if ($this->from === null || $this->to === null || $this->subject === null || $this->body === null) {
            return SendResult::failure('Missing required fields: from, to, subject, body');
        }

        $message = new MimeMessage(
            from   : $this->from,
            to     : $this->to,
            subject: $this->subject,
            body   : $this->body,
            headers: $this->headers,
        );

        return $this->sender->send($message, $this->envelope);
    }
}
