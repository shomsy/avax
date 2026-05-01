<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Queue;

use Avax\Components\Operations\Tasks\System\Capabilities\Queue\Queue;

final readonly class MailQueue
{
    public static function send(Mailable $mailable): string
    {
        return Queue::push(
            job  : static fn (): bool => self::sendNow(mailable: $mailable),
            data : ['mail' => serialize(value: $mailable)],
            queue: 'mail',
        );
    }

    public static function later(int $delay, Mailable $mailable): string
    {
        return Queue::later(
            delay: $delay,
            job  : static fn (): bool => self::sendNow(mailable: $mailable),
            data : ['mail' => serialize(value: $mailable)],
            queue: 'mail',
        );
    }

    public static function sendNow(Mailable $mailable): bool
    {
        $config = function_exists(function: 'config') ? (config(key: 'mail', default: []) ?? []) : [];

        return (new SmtpMailer(config: $config))->send(mailable: $mailable);
    }

    public static function queuedCount(): int
    {
        return Queue::size(queue: 'mail');
    }
}
