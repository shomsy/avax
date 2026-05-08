<?php

declare(strict_types=1);

namespace Avax\Examples\GoldenPathRuntimeApp\Domain\Job;

use Avax\Components\Operations\Queue\System\Foundation\JobInterface;

/**
 * Logs a notification when a webhook is moved to the dead letter queue.
 */
final class DeadLetterNotificationJob implements JobInterface
{
    /**
     * @var list<array{level: string, message: string, context: array<string, mixed>}>
     */
    public static array $log = [];

    /**
     * @param array<string, mixed> $data
     */
    public function handle(array $data = []) : void
    {
        self::$log[] = [
            'level'   => 'error',
            'message' => 'Webhook moved to dead letter queue',
            'context' => [
                'webhook_id' => $data['webhook_id'] ?? 'unknown',
                'attempts'   => $data['attempts'] ?? 0,
            ],
        ];
    }
}
