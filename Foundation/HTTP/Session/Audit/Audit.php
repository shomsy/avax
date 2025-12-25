<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Audit;

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
final class Audit
{

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
    private readonly string|null $logPath;

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
        $payload = [
            'timestamp'   => date(format: 'c'),
            'event'       => strtoupper(string: $event),
            'environment' => getenv(name: 'APP_ENV') ?: 'production',
            'session_id'  => $data['session_id'] ?? null,
            'user_id'     => $data['user_id'] ?? null,
            'ip_address'  => $this->resolveClientIp(),
            'action_data' => $this->sanitize(data: $data),
        ];

        try {
            // ✅ PSR-3 structured log entry
            $this->logger->info(message: '[SESSION_AUDIT]', context: $payload);
        } catch (Throwable $e) {
            // ⚠️ Fail-safe fallback — write to file if possible
            if ($this->logPath) {
                $this->writeToFile(payload: $payload);
            } else {
                error_log(message: "⚠️ [Audit] Logging failed: {$e->getMessage()}");
            }
        }
    }

    /**
     * Determine the client's IP address safely.
     *
     * Handles typical proxy headers to extract the real IP
     * while remaining compatible with direct connections.
     *
     * @return string|null The detected client IP, or null if not available.
     */
    private function resolveClientIp() : string|null
    {
        return $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? null;
    }

    // -------------------------------------------------------------------------
    // 🧠 AUDIT LOGIC
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
            if (is_array(value: $value)) {
                $clean[$key] = $this->sanitize(data: $value);
                continue;
            }

            $clean[$key] = in_array(needle: $key, haystack: $blacklist, strict: true)
                ? '***MASKED***'
                : $value;
        }

        return $clean;
    }

    // -------------------------------------------------------------------------
    // 🧩 HELPERS
    // -------------------------------------------------------------------------

    /**
     * Write the audit payload to file in JSON format.
     *
     * @param array<string, mixed> $payload Structured log data.
     *
     * @return void
     */
    private function writeToFile(array $payload) : void
    {
        $json = json_encode(value: $payload, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $dir  = dirname(path: $this->logPath);

        if (! is_dir(filename: $dir)) {
            mkdir(directory: $dir, permissions: 0775, recursive: true);
        }

        @file_put_contents(filename: $this->logPath, data: $json . PHP_EOL, flags: FILE_APPEND | LOCK_EX);
    }

    /**
     * Resolve the current user's ID from session context.
     *
     * Attempts to infer `user_id` automatically from PHP session data.
     * If unavailable, returns null.
     *
     * @return int|null The current user's ID, or null if unknown.
     */
    private function resolveUserId() : int|null
    {
        return $_SESSION['user_id'] ?? null;
    }
}
