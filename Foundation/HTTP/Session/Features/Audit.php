<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features;

use Avax\HTTP\Session\Contracts\FeatureInterface;

/**
 * Audit - Lightweight Session Audit Logger
 *
 * Provides simple audit logging for session operations.
 * Logs are written to PSR-3 logger, file, or error_log.
 *
 * @example With PSR-3 logger
 *   $audit = new Audit(logger: $psrLoggerInstance);
 *
 * @example With file
 *   $audit = new Audit(logPath: '/var/log/session.log');
 *
 * @package Avax\HTTP\Session
 */
final class Audit implements FeatureInterface
{
    /**
     * @var bool Feature enabled state
     */
    private bool $enabled = true;

    /**
     * Audit Constructor.
     *
     * @param string|null $logPath Optional log file path. Uses error_log if null and no logger.
     * @param object|null $logger  Optional PSR-3 compatible logger.
     */
    public function __construct(
        private string|null $logPath = null,
        private object|null $logger = null
    ) {}

    /**
     * {@inheritdoc}
     */
    public function boot() : void
    {
        $this->enabled = true;
        $this->record('audit_enabled');
    }

    /**
     * Record an audit event.
     *
     * @param string               $event Event name (e.g., 'stored', 'retrieved', 'deleted').
     * @param array<string, mixed> $data  Event context data.
     *
     * @return void
     */
    public function record(string $event, array $data = []) : void
    {
        $timestamp  = date('c');
        $eventUpper = strtoupper($event);
        $context    = empty($data) ? '' : ' ' . json_encode($data);

        $message = sprintf(
            "[%s] SESSION_%s%s",
            $timestamp,
            $eventUpper,
            $context
        );

        // PSR-3 logger takes priority
        if ($this->logger !== null && method_exists($this->logger, 'info')) {
            $this->logger->info($message, $data);

            return;
        }

        // File logging with error handling
        if ($this->logPath !== null) {
            if (! @file_put_contents($this->logPath, $message . "\n", FILE_APPEND)) {
                error_log("Failed to write session audit log to {$this->logPath}");
            }

            return;
        }

        // Fallback to error_log
        error_log($message);
    }

    /**
     * {@inheritdoc}
     */
    public function terminate() : void
    {
        $this->record('audit_terminated');
        $this->enabled = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return 'audit';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled() : bool
    {
        return $this->enabled;
    }
}
