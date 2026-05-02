<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Notifications\System\Capabilities;

/**
 * Interface for notification channels.
 */
interface NotificationChannel
{
    /**
     * Send a notification to a recipient.
     */
    public function send(mixed $notifiable, Notification $notification) : void;

    /**
     * Get the channel name.
     */
    public function name() : string;
}
