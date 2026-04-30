<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Notifications\System\Capabilities\Channels;

use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;
use Avax\Components\Operations\Mail\System\Capabilities\Transport\MailTransport;
use Avax\Components\Operations\Notifications\System\Capabilities\Notification;
use Avax\Components\Operations\Notifications\System\Capabilities\NotificationChannel;

/**
 * Mail channel for sending notifications via email.
 */
final readonly class MailChannel implements NotificationChannel
{
    public function __construct(
        private MailTransport $transport,
        private string $fromAddress = 'notifications@example.com',
    ) {}

    public function send(mixed $notifiable, Notification $notification) : void
    {
        $mailContent = $notification->toMail();
        if ($mailContent === null) {
            return;
        }

        $email = $this->resolveEmail($notifiable);
        if ($email === null) {
            return;
        }

        $subject = $mailContent->subject ?: 'Notification';
        $body    = $this->buildBody($mailContent);

        $message = new MimeMessage(
            from       : $this->fromAddress,
            to         : $email,
            subject    : $subject,
            body       : $body,
            contentType: 'text/html',
        );

        // Create a minimal envelope
        $envelope = new class ($this->fromAddress, $email) {
            public function __construct(
                public readonly string $from,
                public readonly string $to,
            ) {}
        };

        $this->transport->send($message, $envelope);
    }

    private function resolveEmail(mixed $notifiable) : string|null
    {
        if (is_string($notifiable)) {
            return $notifiable;
        }

        if (is_object($notifiable)) {
            if (property_exists($notifiable, 'email')) {
                return $notifiable->email;
            }
            if (method_exists($notifiable, 'getEmail')) {
                return $notifiable->getEmail();
            }
            if (method_exists($notifiable, 'routeNotificationFor')) {
                return $notifiable->routeNotificationFor('mail');
            }
        }

        return null;
    }

    private function buildBody(MailNotificationContent $content) : string
    {
        $html = '<html><body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">';

        if ($content->greeting !== '') {
            $html .= "<h2>{$content->greeting}</h2>";
        }

        if ($content->line !== '') {
            $html .= "<p>{$content->line}</p>";
        }

        foreach ($content->lines as $line) {
            $html .= "<p>{$line}</p>";
        }

        if ($content->actionText !== '' && $content->actionUrl !== '') {
            $html .= '<p><a href="' . htmlspecialchars($content->actionUrl) . '" style="display: inline-block; padding: 10px 20px; background-color: #3490dc; color: #ffffff; text-decoration: none; border-radius: 4px;">' . htmlspecialchars($content->actionText) . '</a></p>';
        }

        if ($content->signOff !== '') {
            $html .= "<p>{$content->signOff}</p>";
        }

        $html .= '</body></html>';

        return $html;
    }

    public function name() : string
    {
        return 'mail';
    }
}
