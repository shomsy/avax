<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\SerializeWorkPayload;

use Closure;
use RuntimeException;
use Throwable;

final readonly class DeserializeWorkPayload
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array{action: string, closure: Closure(): mixed}
     */
    public function deserialize(array $data) : array
    {
        $action  = $data['action'] ?? null;
        $payload = $data['payload'] ?? null;

        if (! is_string($action) || ! is_string($payload)) {
            throw new RuntimeException('Invalid payload structure');
        }

        try {
            $closure = unserialize($payload, ['allowed_classes' => true]);

            if (! $closure instanceof Closure) {
                throw new RuntimeException("Deserialized payload for '{$action}' is not a closure");
            }

            return [
                'action'  => $action,
                'closure' => $closure,
            ];
        } catch (Throwable $e) {
            throw new RuntimeException("Failed to deserialize action '{$action}': {$e->getMessage()}", 0, $e);
        }
    }

    public function canDeserialize(string $payload) : bool
    {
        $result = @unserialize($payload);

        return $result !== false && $result instanceof Closure;
    }
}
