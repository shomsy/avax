<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Audit;

use Psr\Log\LoggerInterface;

final readonly class SessionAudit
{
    public function __construct(
        private ?LoggerInterface $logger = null,
    ) {}

    public function record(string $event, array $data = []) : void
    {
        if (! $this->logger instanceof LoggerInterface) {
            return;
        }

        $payload = [
            'event'     => $event,
            'timestamp' => time(),
            'data'      => $data,
        ];

        $this->logger->info(json_encode($payload));
    }
}
