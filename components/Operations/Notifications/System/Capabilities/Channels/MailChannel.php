<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Notifications\System\Capabilities\Channels;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;
use Avax\Components\Operations\Mail\System\Capabilities\Transport\MailTransport;
use Avax\Components\Operations\Notifications\System\Capabilities\MailNotificationContent;
use Avax\Components\Operations\Notifications\System\Capabilities\Notification;
use Avax\Components\Operations\Notifications\System\Capabilities\NotificationChannel;

/**
 * Mail channel for sending notifications via email.
 */
final readonly class MailChannel implements NotificationChannel
{
    public function __construct(
        private MailTransport $mailTransport,
        private string $fromAddress = 'notifications@example.com',
    ) {
    }

    public function send(mixed $notifiable, Notification $notification): void
    {
        $mailContent = $notification->toMail();
        if (! $mailContent instanceof MailNotificationContent) {
            return;
        }

        $email = $this->resolveEmail($notifiable);
        if ($email === null) {
            return;
        }

        $subject = $mailContent->subject ?: 'Notification';
        $body = $this->buildBody($mailContent);

        $mimeMessage = new MimeMessage(
            from       : $this->fromAddress,
            to         : $email,
            subject    : $subject,
            body       : $body,
            contentType: 'text/html',
        );
        $envelope = new Envelope(from: $this->fromAddress);

        $this->mailTransport->send($mimeMessage, $envelope);
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

    private function buildBody(MailNotificationContent $mailNotificationContent): string
    {
        $html = '<html><body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">';

        if ($mailNotificationContent->greeting !== '') {
            $html .= sprintf('<h2>%s</h2>', $mailNotificationContent->greeting);
        }

        if ($mailNotificationContent->line !== '') {
            $html .= sprintf('<p>%s</p>', $mailNotificationContent->line);
        }

        foreach ($mailNotificationContent->lines as $line) {
            $html .= sprintf('<p>%s</p>', $line);
        }

        if ($mailNotificationContent->actionText !== '' && $mailNotificationContent->actionUrl !== '') {
            $html .= '<p><a href="'.htmlspecialchars($mailNotificationContent->actionUrl).'" style="display: inline-block; padding: 10px 20px; background-color: #3490dc; color: #ffffff; text-decoration: none; border-radius: 4px;">'.htmlspecialchars($mailNotificationContent->actionText).'</a></p>';
        }

        if ($mailNotificationContent->signOff !== '') {
            $html .= sprintf('<p>%s</p>', $mailNotificationContent->signOff);
        }

        return $html.'</body></html>';
    }

    public function name(): string
    {
        return 'mail';
    }
}
