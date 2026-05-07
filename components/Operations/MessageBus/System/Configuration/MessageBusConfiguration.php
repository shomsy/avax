<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Configuration;

final readonly class MessageBusConfiguration
{
    public function __construct(
        public bool $enableTransactionMiddleware = true,
        public bool $enableLoggingMiddleware = false,
        public bool $enableValidationMiddleware = false,
        public int  $defaultRetryAttempts = 0,
    ) {}
}
