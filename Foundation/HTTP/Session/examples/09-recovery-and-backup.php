<?php
/**
 * Example 09: Recovery & Backup (Enterprise Edition - Corrected)
 * ============================================================
 *
 * 🧠 Theory:
 * Recovery is the session’s built-in safety system — it ensures your
 * data *never gets lost* even during crashes, failed writes or
 * transaction errors.
 *
 * 💬 Think of it as:
 * “Undo + Backup + Auto-heal” for your session state.
 */

use Avax\Filesystem\Storage\LocalFileStorage;
use Avax\HTTP\Session\Config\SessionConfig;
use Avax\HTTP\Session\Data\FileStore;
use Avax\HTTP\Session\Data\Recovery;
use Avax\HTTP\Session\Features\Audit;
use Avax\HTTP\Session\Lifecycle\SessionProvider;
use Avax\HTTP\Session\Security\CookieManager;
use Avax\HTTP\Session\Security\EncrypterFactory;
use Avax\HTTP\Session\Security\Policies\PolicyGroupBuilder;
use Avax\HTTP\Session\Security\SessionRegistry;
use Avax\HTTP\Session\Security\SessionSignature;

// ----------------------------------------------------
// 1️⃣ Setup: Enterprise-Ready Session Environment
// ----------------------------------------------------

/**
 * 🧩 Components:
 * - LocalFileStorage → manages on-disk persistence
 * - FileStore → filesystem-backed session storage
 * - Recovery → snapshot/rollback manager (core service)
 * - EncrypterFactory → AES-256-GCM encryption with key rotation
 * - SessionConfig → immutable config object
 * - CookieManager → applies Secure/HttpOnly/SameSite cookie flags
 * - Audit → PSR-3 structured logging
 * - PolicyGroupBuilder → defines idle/lifetime limits
 * - SessionSignature → ensures integrity of session IDs
 */

$filesystem = new LocalFileStorage();

$store = new FileStore(
    storage  : $filesystem,
    directory: __DIR__ . '/storage/sessions'
);

// Core recovery engine (not a feature)
$recovery = new Recovery(store: $store);

// Enterprise-grade encryption (AES-256-GCM + rotation)
$encrypter = (new EncrypterFactory())->create();

// Secure cookie defaults
$cookieManager = new CookieManager();
$cookieManager->configureSessionCookie();

// Immutable configuration
$config = new SessionConfig(
    ttl   : 3600,
    secure: true,
);

// Optional supporting services
$audit     = new Audit(logPath: __DIR__ . '/logs/recovery_audit.log');
$signature = new SessionSignature(secretKey: $_ENV['SESSION_SIGNATURE_KEY'] ?? 'default-signature-key');
$registry  = new SessionRegistry(store: $store);

// Policy group (lifetime & idle)
$policies = PolicyGroupBuilder::create()
    ->requireAll()
    ->maxLifetime(seconds: 3600)
    ->maxIdle(seconds: 900)
    ->endGroup()
    ->build();

// Create session provider (main session engine)
$session = new SessionProvider(
    store    : $store,
    config   : $config,
    encrypter: $encrypter,
    recovery : $recovery,
    signature: $signature,
    policies : $policies,
    registry : $registry
);

// Attach only non-core features (observability, logging, metrics)
$session->registerFeature(feature: $audit);

// ----------------------------------------------------
// 🧩 DEMO 1 — Snapshot & Restore
// ----------------------------------------------------
/**
 * 🧠 Snapshot = save point.
 * Recovery lets you rewind to a safe state if corruption or crash occurs.
 */

try {
    $session->put(key: 'cart', value: ['item' => 'Laptop', 'price' => 1299]);
    $session->recovery()->snapshot();
    echo "💾 Snapshot created successfully.\n";

    // Simulate crash
    throw new RuntimeException(message: "💥 Disk I/O failure while saving session!");
} catch (Throwable $e) {
    echo "⚠️ Crash detected: {$e->getMessage()}\n";

    $session->audit()->record(event: 'crash_detected', data: [
        'reason'    => $e->getMessage(),
        'timestamp' => time(),
    ]);

    $session->recovery()->restore();
    echo "✅ Session restored from last snapshot.\n";
}

// ----------------------------------------------------
// 🧩 DEMO 2 — Transaction Safety (All or Nothing)
// ----------------------------------------------------
/**
 * 🧠 Transactions guarantee atomicity — either all operations succeed,
 * or none are applied (rollback ensures data consistency).
 */

try {
    $session->recovery()->snapshot();
    $session->recovery()->beginTransaction();

    $session->put(key: 'user_id', value: 42);
    $session->put(key: 'cart', value: ['item' => 'Headphones', 'price' => 199]);

    // Uncomment to simulate failure
    // throw new RuntimeException("💥 Payment gateway timeout!");

    $session->recovery()->commit();
    echo "✅ Transaction committed successfully.\n";
} catch (Throwable $e) {
    echo "🚨 Transaction error: {$e->getMessage()}\n";

    $session->audit()->record(event: 'transaction_rollback', data: [
        'error'     => $e->getMessage(),
        'timestamp' => time(),
    ]);

    $session->recovery()->rollback();
    echo "↩️ Rolled back session to previous state.\n";
}

// ----------------------------------------------------
// 🧩 DEMO 3 — Automatic Recovery Integration
// ----------------------------------------------------
/**
 * 🧠 Recovery automatically restores the session from the
 * last valid snapshot if data corruption or crash is detected.
 */

try {
    echo "🚀 Performing high-risk operation...\n";
    $session->put(key: 'temporary', value: ['step' => 1]);
    throw new RuntimeException(message: "💥 Random crash occurred!");
} catch (Throwable $e) {
    echo "⚠️ System failure detected: {$e->getMessage()}\n";
    $session->recovery()->restore();
    echo "✅ Session auto-restored from last known safe point.\n";
}

// ----------------------------------------------------
// 🧾 Final Recap
// ----------------------------------------------------
/**
 * ✅ snapshot() → Save point
 * ✅ restore() → Roll back to last safe state
 * ✅ transaction() → Commit or rollback atomically
 *
 * 🛡️ Enterprise Stack:
 * - AES-256-GCM encryption (EncrypterFactory)
 * - Recovery engine (core, not feature)
 * - Audit trail (PSR-3)
 * - HMAC session integrity (SessionSignature)
 * - Cookie hardening (CookieManager)
 * - Policy-based expiry (PolicyGroupBuilder)
 * - Registry tracking (SessionRegistry)
 */

echo "\n✅ Enterprise Recovery & Backup demo completed.\n";
