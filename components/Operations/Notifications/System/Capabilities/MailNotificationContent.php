<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Notifications\System\Capabilities;

/**
 * Mail notification content.
 */
final class MailNotificationContent
{
    public function __construct(
        public string $subject = '',
        public string $line = '',
        /** @var list<string> */
        public array  $lines = [],
        public string $actionText = '',
        public string $actionUrl = '',
        public string $greeting = '',
        public string $signOff = '',
    )
    {
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function line(string $line): self
    {
        $this->line = $line;

        return $this;
    }

    public function lines(array $lines): self
    {
        $this->lines = $lines;

        return $this;
    }

    public function action(string $text, string $url): self
    {
        $this->actionText = $text;
        $this->actionUrl = $url;

        return $this;
    }

    public function greeting(string $greeting): self
    {
        $this->greeting = $greeting;

        return $this;
    }

    public function signOff(string $signOff): self
    {
        $this->signOff = $signOff;

        return $this;
    }
}
