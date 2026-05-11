<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Notifications\System\Capabilities;

/**
 * Base class for notifications.
 */
abstract class Notification
{
    protected string $id;

    public function __construct()
    {
        $this->id = uniqid('notif-', true);
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the channels the notification should be delivered through.
     *
     * @return list<string>
     */
    abstract public function via(): array;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail() : MailNotificationContent|null
    {
        return null;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase() : array|null
    {
        return null;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(): array
    {
        return [];
    }
}
