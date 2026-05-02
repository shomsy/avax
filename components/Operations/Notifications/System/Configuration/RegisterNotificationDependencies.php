<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Notifications\System\Configuration;

use Avax\Components\Operations\Notifications\System\PublicSurface\Notifier;

final class RegisterNotificationDependencies
{
    public static function register(NotificationChannel $notificationChannel) : void
    {
        Notifier::setChannel($notificationChannel);
    }
}
