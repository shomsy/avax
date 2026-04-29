<?php
namespace Avax\Components\Operations\Notifications\System\Flows;
final class SendNotification { public static function execute(string $message): void { \Avax\Components\Operations\Notifications\System\PublicSurface\Notifier::notify($message); } }
