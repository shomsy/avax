<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Queue;

final readonly class Mail
{
    public static function to(string $to): MailableBuilder
    {
        return new MailableBuilder(to: $to);
    }

    public static function send(Mailable $mailable): string
    {
        return MailQueue::send(mailable: $mailable);
    }

    public static function later(int $delay, Mailable $mailable): string
    {
        return MailQueue::later(delay: $delay, mailable: $mailable);
    }

    public static function sendNow(Mailable $mailable): bool
    {
        return MailQueue::sendNow(mailable: $mailable);
    }
}
