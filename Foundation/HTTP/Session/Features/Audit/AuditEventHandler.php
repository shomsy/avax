<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Audit;

use Avax\HTTP\Session\Features\Events\SessionEvent;

/**
 * AuditEventHandler
 *
 * Handles audit events and logs them.
 *
 * @package Avax\HTTP\Session\Features\Audit
 */
final class AuditEventHandler
{
    /**
     * AuditEventHandler Constructor.
     *
     * @param callable $logger Logger callback.
     */
    public function __construct(
        private $logger
    ) {}

    /**
     * Handle audit event.
     *
     * @param SessionEvent $event The event.
     *
     * @return void
     */
    public function handle(SessionEvent $event): void
    {
        ($this->logger)($event->toArray());
    }

    /**
     * Create with JSON formatter.
     *
     * @param string $logPath Log file path.
     *
     * @return self
     */
    public static function withJsonFormatter(string $logPath): self
    {
        return new self(function (array $data) use ($logPath) {
            file_put_contents(
                $logPath,
                json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL,
                FILE_APPEND
            );
        });
    }
}
