<?php
/**
 * Example 10: Observability & Self-Healing (Real Implementation)
 *
 * 🧠 THEORY:
 * Observability means your system can *see itself* —
 * it can tell you what’s happening inside, why it’s happening,
 * and recover automatically when something goes wrong.
 *
 * You already have observability components — they’re just hidden in plain sight:
 *
 * - 🕵️ Audit → Records everything that happens (the “black box”)
 * - ⚡ Events → Emits real-time signals (the “nervous system”)
 * - 🩹 Recovery → Restores the system when something fails (the “immune system”)
 *
 * Together, these make your Session component *self-aware and self-healing*.
 */

use Avax\Filesystem\Storage\LocalFileStorage;
use Avax\HTTP\Session\Config\SessionConfig;
use Avax\HTTP\Session\Data\FileStore;
use Avax\HTTP\Session\Data\Recovery;
use Avax\HTTP\Session\Features\Audit;
use Avax\HTTP\Session\Features\Events;
use Avax\HTTP\Session\Lifecycle\SessionProvider;
use Avax\HTTP\Session\Security\EncrypterFactory;

// -------------------------------------------------------------
// 1️⃣ SETUP — Build the observability stack
// -------------------------------------------------------------

/**
 * 🧩 The FileStore is your “memory drive”.
 * Audit is your “black box”.
 * Events is your “event bus” (real-time signaling).
 * Recovery is your “backup & immune system”.
 *
 * 🧠 But note:
 * Recovery is *not* a Session Feature — it’s a helper service.
 * It doesn’t “live inside” the session engine like Audit or Events.
 * Think of it as a mechanic who can repair the system from the outside.
 */

// 1️⃣ Create proper storage backend
$filesystem = new LocalFileStorage();

// 2️⃣ Pass it to FileStore (acts as the bridge between session and filesystem)
$store = new FileStore(
    storage  : $filesystem,
    directory: __DIR__ . '/storage/sessions'
);

// 3️⃣ Initialize observability components
$audit    = new Audit(logPath: __DIR__ . '/logs/session/audit_observability.log');
$events   = new Events();
$recovery = new Recovery(store: $store); // ✅ helper, not feature

// 4️⃣ Build the session provider (correct parameters)

// 4.1 Create encryption layer
$encrypter = (new EncrypterFactory())->create();

// 4.2 Basic immutable session config (could be tuned per env)
$config = new SessionConfig(
    ttl   : 3600,   // 1 hour
    secure: true,   // HTTPS-only cookies
);

// 4.3 Recovery engine (already initialized above)
$recovery = new Recovery(store: $store);

// 4.4 Construct the main session engine
$session = new SessionProvider(
    store    : $store,
    config   : $config,
    encrypter: $encrypter,
    recovery : $recovery
);

// Register observability features
$session->registerFeature(feature: $audit);
$session->registerFeature(feature: $events);
/**
 * -------------------------------------------------------------
 * 2️⃣ AUDIT — The “black box recorder”
 * -------------------------------------------------------------
 *
 * 🧠 Concept:
 * The Audit feature logs every meaningful session action — put, get, flush, etc.
 * If something goes wrong, you can open its log and replay the story.
 *
 * 💬 Think of it as a plane’s black box ✈️ —
 * it doesn’t stop accidents, but it explains what happened.
 *
 * 🧱 Real-world analogy:
 * When a pilot reports “engine failure”, investigators check the black box.
 * When a user reports “I got logged out”, you check the audit log.
 */

$session->audit()->record(event: 'session_start', data: ['user_id' => 42]);
$session->put(key: 'user_id', value: 42);
$session->audit()->record(event: 'cart_update', data: ['action' => 'added item', 'item' => 'Laptop']);

/**
 * -------------------------------------------------------------
 * 3️⃣ EVENTS — The “nervous system”
 * -------------------------------------------------------------
 *
 * 🧠 Concept:
 * The Events system emits real-time “signals” whenever something happens.
 * For example: “stored”, “deleted”, “flushed”, “expired”.
 *
 * 💬 Think of it like the nerves in your body —
 * they instantly notify you if something’s hot, cold, or broken.
 *
 * 🧱 Real-world analogy:
 * Imagine touching something too hot 🔥 — your hand pulls away instantly.
 * That’s how events allow instant reactions in your code.
 */

$session->events()->listen(event: 'stored', callback: static function ($data) {
    echo "📢 [Event] Key '{$data['key']}' was stored.\n";
});

$session->events()->listen(event: 'flushed', callback: static function () {
    echo "🧹 [Event] All session data cleared.\n";
});

// Trigger some actions
$session->put(key: 'cart', value: ['item' => 'Laptop', 'price' => 1200]);
$session->flush();

/**
 * -------------------------------------------------------------
 * 4️⃣ RECOVERY — The “immune system”
 * -------------------------------------------------------------
 *
 * 🧠 Concept:
 * Recovery automatically takes snapshots (backups) and can restore data
 * if the session is corrupted or a crash happens.
 *
 * 💬 Think of it like an “undo button” for your session.
 *
 * 🧱 Real-world analogy:
 * Imagine writing a long essay on your laptop, and the power goes out 💥.
 * Auto-save brings everything back — that’s Recovery.
 *
 * ⚙️ Problem it solves:
 * Prevents total data loss due to exceptions, server errors, or backend issues.
 */

try {
    $session->put(key: 'checkout_step', value: 'shipping');
    $recovery->backup(); // ✅ make a safe snapshot

    // Simulate a crash
    throw new RuntimeException(message: '💥 Disk failure while writing session!');
} catch (Throwable $e) {
    echo "⚠️ Error detected: {$e->getMessage()}\n";

    // ✅ Roll back to previous working state
    $recovery->restore();
    echo "✅ Session state recovered successfully.\n";
}

/**
 * -------------------------------------------------------------
 * 5️⃣ SELF-HEALING — Bringing it all together
 * -------------------------------------------------------------
 *
 * 🧠 Concept:
 * When something bad happens:
 *   - Events detect it instantly (reflex)
 *   - Audit records the details (memory)
 *   - Recovery restores the last safe state (healing)
 *
 * 💬 In plain English:
 * “The session system fell, noticed it, healed itself, and told you why.”
 *
 * 🧱 Real-world analogy:
 * Like a self-driving car 🚗 that detects a flat tire, slows down safely,
 * switches to backup power, and sends a diagnostic report.
 */

$events->listen(event: 'crash_detected', callback: function ($context) use ($audit) {
    $audit->record(event: 'crash_event', data: [
        'reason' => $context['reason'] ?? 'unknown',
        'time'   => date(format: 'c')
    ]);

    echo "🛠️ Auto-heal triggered for crash: {$context['reason']}\n";
});

// Simulate “self-healing event”
$events->dispatch(event: 'crash_detected', data: ['reason' => 'database timeout']);

/**
 * -------------------------------------------------------------
 * 🧾 SUMMARY
 * -------------------------------------------------------------
 * ✅ Audit — records “what happened”
 * ✅ Events — notify “when it happens”
 * ✅ Recovery — restores “when it fails”
 *
 * 🧠 Together, these make your Session *self-observing* and *resilient*.
 *
 * 💬 Think of them like:
 * - The session can now *talk*, *remember*, and *heal*.
 * - You don’t just store data — you manage a living, thinking system.
 */

echo "\n✅ Observability & Self-Healing demo completed successfully.\n";
