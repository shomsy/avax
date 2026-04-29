<?php
namespace Avax\Components\Operations\Notifications\System\Capabilities;
interface NotificationChannel { public function send(string $message): void; public function sendTo(string $recipient, string $message): void; }
