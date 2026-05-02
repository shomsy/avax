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

    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Get the channels the notification should be delivered through.
     *
     * @return list<string>
     */
    abstract public function via() : array;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail() : ?MailNotificationContent
    {
        return null;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase() : ?array
    {
        return null;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray() : array
    {
        return [];
    }
}

/**
 * Mail notification content.
 */
final class MailNotificationContent
{
    public function __construct(
        public string $subject = '',
        public string $line = '',
        /** @var list<string> */
        public array $lines = [],
        public string $actionText = '',
        public string $actionUrl = '',
        public string $greeting = '',
        public string $signOff = '',
    ) {}

    public function subject(string $subject) : self
    {
        $this->subject = $subject;

        return $this;
    }

    public function line(string $line) : self
    {
        $this->line = $line;

        return $this;
    }

    public function lines(array $lines) : self
    {
        $this->lines = $lines;

        return $this;
    }

    public function action(string $text, string $url) : self
    {
        $this->actionText = $text;
        $this->actionUrl = $url;

        return $this;
    }

    public function greeting(string $greeting) : self
    {
        $this->greeting = $greeting;

        return $this;
    }

    public function signOff(string $signOff) : self
    {
        $this->signOff = $signOff;

        return $this;
    }
}
