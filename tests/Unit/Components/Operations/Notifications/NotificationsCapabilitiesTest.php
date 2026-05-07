<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Notifications;

use Avax\Components\Operations\Notifications\System\Capabilities\Notification;
use Avax\Components\Operations\Notifications\System\Capabilities\NotificationChannel;
use Avax\Components\Operations\Notifications\System\Flows\SendNotification;
use Avax\Components\Operations\Notifications\System\Foundation\NotificationException;
use PHPUnit\Framework\TestCase;

final class NotificationsCapabilitiesTest extends TestCase
{
    public function test_it_sends_notification_through_registered_channel() : void
    {
        $flow = new SendNotification();
        $sent = false;

        $channel = new class($sent) implements NotificationChannel {
            private bool $sentRef;

            public function __construct(private bool &$sent)
            {
                $this->sentRef = &$this->sent;
            }

            public function send(mixed $notifiable, Notification $notification) : void
            {
                $this->sent = true;
            }

            public function name() : string
            {
                return 'test';
            }
        };

        $notification = new class extends Notification {
            public function via() : array
            {
                return ['test'];
            }
        };

        $flow->registerChannel($channel);
        $flow->sendTo('user@example.com', $notification);
        $this->assertTrue($sent);
    }

    public function test_it_throws_when_channel_is_not_registered() : void
    {
        $flow         = new SendNotification();
        $notification = new class extends Notification {
            public function via() : array
            {
                return ['sms'];
            }
        };

        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('sms');
        $flow->sendTo('user', $notification);
    }

    public function test_it_checks_channel_existence() : void
    {
        $flow = new SendNotification();
        $this->assertFalse($flow->hasChannel('email'));

        $channel = new class implements NotificationChannel {
            public function send(mixed $notifiable, Notification $notification) : void {}

            public function name() : string
            {
                return 'email';
            }
        };

        $flow->registerChannel($channel);
        $this->assertTrue($flow->hasChannel('email'));
    }

    public function test_it_sends_to_specific_channel_when_overridden() : void
    {
        $flow    = new SendNotification();
        $sentVia = [];

        $mailChannel = new class($sentVia) implements NotificationChannel {
            /** @param array<int, string> $sentVia */
            public function __construct(private array &$sentVia) {}

            public function send(mixed $notifiable, Notification $notification) : void
            {
                $this->sentVia[] = 'mail';
            }

            public function name() : string
            {
                return 'mail';
            }
        };

        $smsChannel = new class($sentVia) implements NotificationChannel {
            /** @param array<int, string> $sentVia */
            public function __construct(private array &$sentVia) {}

            public function send(mixed $notifiable, Notification $notification) : void
            {
                $this->sentVia[] = 'sms';
            }

            public function name() : string
            {
                return 'sms';
            }
        };

        $notification = new class extends Notification {
            public function via() : array
            {
                return ['mail', 'sms'];
            }
        };

        $flow->registerChannel($mailChannel);
        $flow->registerChannel($smsChannel);

        // Override to only use sms
        $flow->sendTo('user', $notification, 'sms');
        $this->assertSame(['sms'], $sentVia);
    }

    public function test_it_generates_unique_notification_id() : void
    {
        $notification1 = new class extends Notification {
            public function via() : array
            {
                return [];
            }
        };
        $notification2 = new class extends Notification {
            public function via() : array
            {
                return [];
            }
        };

        $this->assertNotEmpty($notification1->getId());
        $this->assertNotSame($notification1->getId(), $notification2->getId());
    }
}
