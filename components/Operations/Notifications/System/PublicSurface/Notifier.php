<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Notifications\System\PublicSurface;

use Avax\Components\Operations\Notifications\System\Capabilities\Notification;
use Avax\Components\Operations\Notifications\System\Capabilities\NotificationChannel;
use Avax\Components\Operations\Notifications\System\Flows\SendNotification;

/**
 * Notifier - main entry point for sending notifications.
 */
class Notifier
{
    private SendNotification $flow;

    public function __construct()
    {
        $this->flow = new SendNotification();
    }

    public function registerChannel(NotificationChannel $channel) : self
    {
        $this->flow->registerChannel($channel);

        return $this;
    }

    public function sendTo(mixed $notifiable, Notification $notification, string $channel = null) : void
    {
        $this->flow->sendTo($notifiable, $notification, $channel);
    }

    public function sendToMany(array $notifiables, Notification $notification, string $channel = null) : void
    {
        $this->flow->sendToMany($notifiables, $notification, $channel);
    }

    public function hasChannel(string $name) : bool
    {
        return $this->flow->hasChannel($name);
    }
}
