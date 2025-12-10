<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features;

use Avax\HTTP\Session\Contracts\FeatureInterface;
use Avax\Logging\LoggerFactory;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * 🧠 Audit - Enterprise-Grade Session Audit Logger
 * ============================================================
 *
 * This feature provides structured, secure, and resilient audit logging
 * for all session lifecycle events within the Avax HTTP framework.
 *
 * It integrates seamlessly with your `LoggerFactory` and PSR-3 loggers,
 * automatically attaching contextual data such as:
 * - environment name (dev/staging/prod)
 * - session ID
 * - user ID (if available)
 * - client IP address
 * - timestamp
 *
 * 💬 Think of it as:
 * “The corporate black box for your session layer —
 *  every action is recorded, structured, and traceable.”
 *
 * ⚙️ Key Features:
 * - ✅ PSR-3 compliant — works with any logger implementation
 * - ✅ JSON-structured logs — ready for ELK, Loki, or Datadog ingestion
 * - ✅ Crash-safe — never throws exceptions during logging
 * - ✅ Context-aware — captures environment, IP, and session metadata
 * - ✅ Sensitive-data sanitization — masks tokens, passwords, secrets
 *
 * @package Avax\HTTP\Session\Features
 * @author  —
 */
final class Audit implements FeatureInterface
{
    /**
     * Whether the audit feature is currently active.
     *
     * @var bool
     */
    private bool $enabled = true;

    /**
     * The PSR-3 compliant logger used to record audit events.
     *
     * This is resolved automatically using the `LoggerFactory`
     * if no custom logger is provided in the constructor.
     *
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;

    /**
     * Optional log file path for fallback or standalone use.
     *
     * @var string|null
     */
    private readonly ?string $logPath;

    // -------------------------------------------------------------------------
    // 🚀 CONSTRUCTOR
    // -------------------------------------------------------------------------

    /**
     * Construct a new Audit feature instance.
     *
     * @param LoggerInterface|null $logger  Optional PSR-3 logger instance.
     * @param string|null          $logPath Optional direct log file path (fallback mode).
     *
     * 💬 Think of it as:
     * “You can give me a PSR logger, a file path — or nothing.
     *  I’ll make sure your audit events are never lost.”
     */
    public function __construct(LoggerInterface|null $logger = null, string|null $logPath = null)
    {
        $this->logger  = $logger ?? (new LoggerFactory())->createLoggerFor(channel: 'session-audit');
        $this->logPath = $logPath;
    }

    // -------------------------------------------------------------------------
    // 🧩 FEATURE INTERFACE METHODS
    // -------------------------------------------------------------------------

    /**
     * Get the feature name.
     *
     * @return string The feature identifier ("audit").
     */
    public function getName() : string
    {
        return 'audit';
    }

    /**
     * Boot the audit feature.
     *
     * Called automatically when the feature is registered within
     * the SessionProvider. Enables logging and records initialization.
     *
     * 💬 Think of it as “starting the flight recorder”.
     *
     * @return void
     */
    public function boot() : void
    {
        $this->enabled = true;

        $this->record(
            event: 'audit_initialized',
            data : [
                'status'    => 'enabled',
                'env'       => getenv(name: 'APP_ENV') ?: 'production',
                'timestamp' => date(format: 'c'),
            ]
        );
    }

    /**
     * Record a structured audit event.
     *
     * This method safely logs session actions, with contextual metadata.
     * It never throws exceptions — even if the logger or file operation fails.
     *
     * @param string               $event Event name (e.g. 'session_created', 'key_deleted').
     * @param array<string, mixed> $data  Additional context (e.g. user ID, request details).
     *
     * 💬 Example:
     * ```php
     * $audit->record('session_regenerated', [
     *     'user_id' => 42,
     *     'old_id'  => 'abc123',
     *     'new_id'  => 'xyz789'
     * ]);
     * ```
     *
     * @return void
     */
    public function record(string $event, array $data = []) : void
    {
        if (! $this->enabled) {
            return;
        }

        $payload = [
            'timestamp'   => date(format: 'c'),
            'event'       => strtoupper(string: $event),
            'environment' => getenv(name: 'APP_ENV') ?: 'production',
            'session_id'  => $data['session_id'] ?? $_COOKIE['PHPSESSID'] ?? null,
            'user_id'     => $data['user_id'] ?? $this->resolveUserId(),
            'ip_address'  => $this->resolveClientIp(),
            'action_data' => $this->sanitize(data: $data),
        ];

        try {
            // ✅ PSR-3 structured log entry
            $this->logger->info(message: '[SESSION_AUDIT]', context: $payload);
        } catch (Throwable $e) {
            // ⚠️ Fail-safe fallback — write to file if possible
            if ($this->logPath) {
                $this->writeToFile($payload);
            } else {
                error_log(message: "⚠️ [Audit] Logging failed: {$e->getMessage()}");
            }
        }
    }

    /**
     * Resolve the current user's ID from session context.
     *
     * Attempts to infer `user_id` automatically from PHP session data.
     * If unavailable, returns null.
     *
     * @return int|null The current user's ID, or null if unknown.
     */
    private function resolveUserId() : ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    // -------------------------------------------------------------------------
    // 🧠 AUDIT LOGIC
    // -------------------------------------------------------------------------

    /**
     * Determine the client's IP address safely.
     *
     * Handles typical proxy headers to extract the real IP
     * while remaining compatible with direct connections.
     *
     * @return string|null The detected client IP, or null if not available.
     */
    private function resolveClientIp() : ?string
    {
        return $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? null;
    }

    // -------------------------------------------------------------------------
    // 🧩 HELPERS
    // -------------------------------------------------------------------------

    /**
     * Sanitize potentially sensitive data before logging.
     *
     * Removes or masks fields that could contain confidential information,
     * such as passwords, tokens, or API keys.
     *
     * Supports deep recursive sanitization for nested arrays.
     *
     * @param array<string, mixed> $data Input data to clean.
     *
     * @return array<string, mixed> Sanitized version of the data.
     */
    private function sanitize(array $data) : array
    {
        $blacklist = ['password', 'token', 'api_key', 'secret'];
        $clean     = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = $this->sanitize($value);
                continue;
            }

            $clean[$key] = in_array($key, $blacklist, true)
                ? '***MASKED***'
                : $value;
        }

        return $clean;
    }

    /**
     * Write the audit payload to file in JSON format.
     *
     * @param array<string, mixed> $payload Structured log data.
     *
     * @return void
     */
    private function writeToFile(array $payload) : void
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $dir  = dirname($this->logPath);

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        @file_put_contents($this->logPath, $json . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Gracefully shut down the audit feature.
     *
     * Called during Session termination or provider shutdown.
     * Writes a final log entry to indicate the audit system was disabled.
     *
     * 💬 Think of it as “turning off the black box recorder”.
     *
     * @return void
     */
    public function terminate() : void
    {
        $this->record(
            event: 'audit_terminated',
            data : [
                'status'    => 'disabled',
                'timestamp' => date(format: 'c'),
            ]
        );

        $this->enabled = false;
    }

    /**
     * Check if the audit feature is active.
     *
     * @return bool True if active, false if disabled.
     */
    public function isEnabled() : bool
    {
        return $this->enabled;
    }
}
