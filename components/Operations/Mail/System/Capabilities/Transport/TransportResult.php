<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Transport;

final readonly class TransportResult
{
    public function __construct(
        public bool $success,
        public string|null $messageId = null,
        public string|null $error = null,
    ) {
    }
}
