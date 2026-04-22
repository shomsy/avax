<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Audit;

/**
 * 🧠 AuditManager - Session Audit Operations Orchestrator
 * ============================================================
 *
 * The AuditManager orchestrates all session auditing and logging
 * operations for compliance, security, and debugging purposes.
 *
 * This manager provides:
 * - Structured event logging with context
 * - PSR-3 logger integration
 * - Sensitive data sanitization
 * - Audit trail management
 *
 * 💡 Design Philosophy:
 * Audit logging is critical for security compliance (GDPR, SOC2, etc.)
 * and debugging. This manager ensures all session operations are
 * properly logged without impacting performance or exposing sensitive data.
 *
 * @author  Milos
 *
 * @version 5.0
 */
final readonly class AuditManager
{
    private Audit $audit;

    /**
     * AuditManager Constructor.
     *
     * @param Audit $audit The audit feature instance.
     */
    public function __construct(Audit $audit) { $this->audit = $audit; }

    // ----------------------------------------------------------------
    // 🔹 Lifecycle Management
    // ----------------------------------------------------------------

    /**
     * Record a session event with contextual data.
     *
     * Logs the event with automatic context enrichment including:
     * - Timestamp
     * - User ID (if available)
     * - Client IP address
     * - Session ID
     *
     * Sensitive data is automatically sanitized before logging.
     *
     * @param string               $event Event name (e.g., 'session.put', 'login').
     * @param array<string, mixed> $data  Event-specific data.
     */
    public function record(string $event, array $data = []) : void
    {
        $this->audit->record(event: $event, data: $data);
    }

    /**
     * Disable audit logging.
     *
     * Stops recording events and optionally writes a final log entry.
     *
     * @return self Fluent interface.
     */
    public function disable() : self
    {
        $this->audit->terminate();

        return $this;
    }

    // ----------------------------------------------------------------
    // 🔹 Internal Access
    // ----------------------------------------------------------------

    /**
     * Get the underlying Audit instance.
     *
     * Provides direct access to the audit feature for advanced operations.
     *
     * @return Audit The audit instance.
     */
    public function audit() : Audit
    {
        return $this->audit;
    }
}
