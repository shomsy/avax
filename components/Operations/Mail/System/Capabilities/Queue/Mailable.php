<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Queue;

final class Mailable
{
    private string $to = '';

    private string $subject = '';

    private string $body = '';

    private ?string $from = null;

    /** @var list<string> */
    private array $cc = [];

    /** @var list<array{path:string,name:string}> */
    private array $attachments = [];

    public function to(string $address): self
    {
        $this->to = $address;

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

    public function from(string $address, string $name = ''): self
    {
        $this->from = $name !== '' ? "{$name} <{$address}>" : $address;

        return $this;
    }

    public function cc(string $address): self
    {
        $this->cc[] = $address;

        return $this;
    }

    public function attach(string $path, string $name = ''): self
    {
        $this->attachments[] = ['path' => $path, 'name' => $name !== '' ? $name : basename(path: $path)];

        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function getCc(): array
    {
        return $this->cc;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }
}
