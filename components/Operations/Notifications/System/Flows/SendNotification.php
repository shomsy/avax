<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Notifications\System\Flows;

use Avax\Components\Operations\Notifications\System\Capabilities\Notification;
use Avax\Components\Operations\Notifications\System\Capabilities\NotificationChannel;
use Avax\Components\Operations\Notifications\System\Foundation\NotificationException;

/**
 * SendNotification flow - orchestrates sending notifications through channels.
 */
final class SendNotification
{
    /** @var array<string, NotificationChannel> */
    private array $channels = [];

    public function registerChannel(NotificationChannel $channel) : self
    {
        $this->channels[$channel->name()] = $channel;

        return $this;
    }

    public function hasChannel(string $name) : bool
    {
        return isset($this->channels[$name]);
    }

    public function sendTo(mixed $notifiable, Notification $notification, ?string $channel = null) : void
    {
        $channels = $channel !== null ? [$channel] : $notification->via();

        foreach ($channels as $channelName) {
            if (! isset($this->channels[$channelName])) {
                throw new NotificationException("Notification channel '{$channelName}' is not registered.");
            }

            $this->channels[$channelName]->send($notifiable, $notification);
        }
    }

    public function sendToMany(array $notifiables, Notification $notification, ?string $channel = null) : void
    {
        foreach ($notifiables as $notifiable) {
            $this->sendTo($notifiable, $notification, $channel);
        }
    }
}
