<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\PublicSurface;

final class MailMessage
{
    private array $data = [
        'from' => [],
        'to' => [],
        'cc' => [],
        'bcc' => [],
        'subject' => '',
        'body' => '',
        'html' => null,
        'attachments' => [],
    ];

    public function from(string $address, string|null $name = null) : self
    {
        $this->data['from'] = $name ? ["$name <$address>" : $address]
        return $this;
    }

    public function to(string $address, string|null $name = null) : self
    {
        $this->data['to'][] = $name ? ["$name <$address>" : $address]
        return $this;
    }

    public function cc(string $address, string|null $name = null) : self
    {
        $this->data['cc'][] = $name ? ["$name <$address>" : $address]
        return $this;
    }

    public function bcc(string $address, string|null $name = null) : self
    {
        $this->data['bcc'][] = $name ? ["$name <$address>" : $address]
        return $this;
    }

    public function subject(string $subject) : self
    {
        $this->data['subject'] = $subject;
        return $this;
    }

    public function body(string $body) : self
    {
        $this->data['body'] = $body;
        return $this;
    }

    public function html(string $html) : self
    {
        $this->data['html'] = $html;
        return $this;
    }

    public function attach(string $path, string|null $name = null) : self
    {
        $this->data['attachments'][] = ['path' => $path, 'name' => $name ?? basename($path)];
        return $this;
    }

    public function send(Mailer $mailer) : void
    {
        $mailer->send($this);
    }

    public function getFrom() : array { return $this->data['from']; }

    public function getTo() : array { return $this->data['to']; }

    public function getCc() : array { return $this->data['cc']; }

    public function getBcc() : array { return $this->data['bcc']; }

    public function getSubject() : string { return $this->data['subject']; }

    public function getBody() : string { return $this->data['body']; }

    public function getHtml() : string|null { return $this->data['html']; }

    public function getAttachments() : array { return $this->data['attachments']; }
}