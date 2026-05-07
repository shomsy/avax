<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Flows\HandleRealtimeMessage;

use Avax\Components\Operations\Realtime\System\Capabilities\Connections\ConnectionPool;

final readonly class HandleRealtimeMessage
{
    /**
     * @param array<string, mixed> $message
     */
    public function handle(ConnectionPool $pool, string $connectionId, array $message) : array
    {
        $connection = $pool->get($connectionId);

        if ($connection === null) {
            return [
                'handled' => false,
                'reason'  => 'Connection not found.',
            ];
        }

        $type    = $message['type'] ?? 'unknown';
        $payload = $message['payload'] ?? [];

        return [
            'handled' => true,
            'type'    => $type,
            'payload' => $payload,
        ];
    }
}
