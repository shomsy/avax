<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionAudit;

final class SessionAudit
{
    private $logger;

    public function __construct($logger = null)
    {
        $this->logger = $logger;
    }

    public function record(string $event, array $data = []) : void
    {
        if ($this->logger === null) {
            return;
        }

        $record = [
            'event'     => $event,
            'timestamp' => time(),
            'data'      => $data,
        ];

        $this->logger->info(json_encode($record));
    }
}