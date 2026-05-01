<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Content;

final class MimeMessage
{
    public function __construct(
        public readonly string $from,
        public readonly string $to,
        public readonly string $subject,
        public readonly string $body,
        public readonly string $contentType = 'text/plain',
        public readonly array $headers = [],
        public readonly array $attachments = [],
        public readonly ?string $replyTo = null,
        public readonly ?string $cc = null,
        public readonly ?string $bcc = null,
    ) {}

    public function withReplyTo(string $replyTo): self
    {
        return new self(
            from       : $this->from,
            to         : $this->to,
            subject    : $this->subject,
            body       : $this->body,
            contentType: $this->contentType,
            headers    : $this->headers,
            attachments: $this->attachments,
            replyTo    : $replyTo,
            cc         : $this->cc,
            bcc        : $this->bcc,
        );
    }

    public function withCc(string $cc): self
    {
        return new self(
            from       : $this->from,
            to         : $this->to,
            subject    : $this->subject,
            body       : $this->body,
            contentType: $this->contentType,
            headers    : $this->headers,
            attachments: $this->attachments,
            replyTo    : $this->replyTo,
            cc         : $cc,
            bcc        : $this->bcc,
        );
    }

    public function withBcc(string $bcc): self
    {
        return new self(
            from       : $this->from,
            to         : $this->to,
            subject    : $this->subject,
            body       : $this->body,
            contentType: $this->contentType,
            headers    : $this->headers,
            attachments: $this->attachments,
            replyTo    : $this->replyTo,
            cc         : $this->cc,
            bcc        : $bcc,
        );
    }

    public function html(string $html): self
    {
        return new self(
            from       : $this->from,
            to         : $this->to,
            subject    : $this->subject,
            body       : $html,
            contentType: 'text/html',
            headers    : $this->headers,
            attachments: $this->attachments,
            replyTo    : $this->replyTo,
            cc         : $this->cc,
            bcc        : $this->bcc,
        );
    }

    public function withAttachment(string $filename, string $content, string $mimeType): self
    {
        $attachments = $this->attachments;
        $attachments[] = [
            'filename' => $filename,
            'content' => $content,
            'mimeType' => $mimeType,
        ];

        return new self(
            from       : $this->from,
            to         : $this->to,
            subject    : $this->subject,
            body       : $this->body,
            contentType: $this->contentType,
            headers    : $this->headers,
            attachments: $attachments,
            replyTo    : $this->replyTo,
            cc         : $this->cc,
            bcc        : $this->bcc,
        );
    }

    public function toRaw(): string
    {
        $lines = [];
        $lines[] = "From: {$this->from}";
        $lines[] = "To: {$this->to}";
        $lines[] = "Subject: {$this->subject}";
        $lines[] = "Content-Type: {$this->contentType}; charset=UTF-8";

        if ($this->replyTo !== null) {
            $lines[] = "Reply-To: {$this->replyTo}";
        }

        if ($this->cc !== null) {
            $lines[] = "Cc: {$this->cc}";
        }

        foreach ($this->headers as $name => $value) {
            $lines[] = "$name: $value";
        }

        $lines[] = '';
        $lines[] = $this->body;

        return implode("\r\n", $lines);
    }
}
