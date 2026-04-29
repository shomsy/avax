<?php
namespace Avax\Components\Operations\Notifications\System\Configuration;
final class RegisterNotificationServices { public static function register(NotificationChannel $channel): void { \Avax\Components\Operations\Notifications\System\PublicSurface\Notifier::setChannel($channel); } }
