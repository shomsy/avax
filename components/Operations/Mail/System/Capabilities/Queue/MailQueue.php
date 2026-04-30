<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Queue;

use Avax\Components\Tasks\System\Capabilities\Queue\Queue;

final class MailQueue
{
    public static function later(int $delay, Mailable $mailable) : string
    {
        $job = static function () use ($mailable) {
            $mailer = new SmtpMailer(config('mail') ?? []);
            $mailer->send($mailable);
        };

        return Queue::later($delay, $job, ['mailable' => serialize($mailable)]);
    }

    public static function send(Mailable $mailable) : string
    {
        $job = static function () use ($mailable) {
            $mailer = new SmtpMailer(config('mail') ?? []);
            $mailer->send($mailable);
        };

        return Queue::push($job, ['mailable' => serialize($mailable)]);
    }

    public static function sendNow(Mailable $mailable) : bool
    {
        $mailer = new SmtpMailer(config('mail') ?? []);

        return $mailer->send($mailable);
    }
}

final class SmtpMailer
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config + [
                'host' => '127.0.0.1',
                'port' => 1025,
                'from' => 'noreply@localhost',
            ];
    }

    public function send(Mailable $mailable) : bool
    {
        $to      = $mailable->getTo();
        $subject = $mailable->getSubject();
        $body    = $mailable->getBody();
        $from    = $this->config['from'];

        $headers = [
            "From: {$from}",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
        ];

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
}

class Mailable
{
    protected string $to          = '';
    protected string $subject     = '';
    protected string $body        = '';
    protected array  $from        = [];
    protected array  $cc          = [];
    protected array  $attachments = [];

    public function to(string $address) : self
    {
        $this->to = $address;

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

    public function from(string $address, string $name = '') : self
    {
        $this->from = $name ? "{$name} <{$address}>" : $address;

        return $this;
    }

    public function cc(string $address) : self
    {
        $this->cc[] = $address;

        return $this;
    }

    public function attach(string $path, string $name = '') : self
    {
        $this->attachments[] = ['path' => $path, 'name' => $name];

        return $this;
    }

    public function getTo() : string
    {
        return $this->to;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function getBody() : string
    {
        return $this->body;
    }

    public function getFrom() : array
    {
        return $this->from;
    }

    public function getCc() : array
    {
        return $this->cc;
    }

    public function getAttachments() : array
    {
        return $this->attachments;
    }
}

final class Mail
{
    public static function to(string $to) : MailableBuilder
    {
        return new MailableBuilder($to);
    }

    public static function send(Mailable $mailable) : string
    {
        return MailQueue::send($mailable);
    }

    public static function later(int $delay, Mailable $mailable) : string
    {
        return MailQueue::later($delay, $mailable);
    }

    public static function sendNow(Mailable $mailable) : bool
    {
        return MailQueue::sendNow($mailable);
    }
}

final class MailableBuilder
{
    private Mailable $mailable;

    public function __construct(string $to)
    {
        $this->mailable = new Mailable();
        $this->mailable->to($to);
    }

    public function subject(string $subject) : self
    {
        $this->mailable->subject($subject);

        return $this;
    }

    public function body(string $body) : self
    {
        $this->mailable->body($body);

        return $this;
    }

    public function send() : string
    {
        return MailQueue::send($this->mailable);
    }

    public function sendNow() : bool
    {
        return MailQueue::sendNow($this->mailable);
    }
}