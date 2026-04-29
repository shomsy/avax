<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Notifications\System\PublicSurface;

final class Notifier
{
    private static ?\Avax\Components\Operations\Notifications\System\Capabilities\NotificationChannel $channel = null;

    public static function setChannel(\Avax\Components\Operations\Notifications\System\Capabilities\NotificationChannel $channel): void
    {
        self::$channel = $channel;
    }

    public static function notify(string $message): void
    {
        self::getChannel()->send($message);
    }

    public static function send(string $recipient, string $message): void
    {
        self::getChannel()->sendTo($recipient, $message);
    }

    private static function getChannel(): \Avax\Components\Operations\Notifications\System\Capabilities\NotificationChannel
    {
        if (self::$channel === null) {
            throw new \RuntimeException('Notification channel not configured.');
        }
        return self::$channel;
    }
}