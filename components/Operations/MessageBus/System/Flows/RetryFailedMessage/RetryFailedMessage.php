<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Flows\RetryFailedMessage;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\CommandBus;
use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;
use Throwable;

final readonly class RetryFailedMessage
{
    /**
     * @return array{success: bool, attempts: int, result: mixed, error: string|null}
     */
    public function retry(CommandBus $bus, Command $command, int $attempts = 3) : array
    {
        $lastException = null;

        for ($i = 0; $i < $attempts; $i++) {
            try {
                $result = $bus->dispatch($command);

                return [
                    'success'  => true,
                    'attempts' => $i + 1,
                    'result'   => $result,
                    'error' => null,
                ];
            } catch (Throwable $e) {
                $lastException = $e;
            }
        }

        return [
            'success'  => false,
            'attempts' => $attempts,
            'result' => null,
            'error'    => $lastException?->getMessage(),
        ];
    }
}
