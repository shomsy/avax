<?php

/**
 * Example 11: Session Lifecycle & Regeneration
 *
 * 🧠 Theory:
 * Every session has a *lifecycle* — it is born, lives, renews itself, and eventually dies.
 *
 * This example demonstrates how the Session system handles:
 * - creation (starting a new session)
 * - regeneration (changing its ID for security)
 * - renewal (extending active sessions)
 * - expiration (ending idle or old sessions)
 * - termination (safe logout & cleanup)
 *
 * 💬 Think of a session like a living organism:
 * - It’s born when a user logs in.
 * - It grows and evolves as you interact with your app.
 * - It renews its “identity” to stay safe.
 * - It dies when it’s no longer needed — to keep the system clean.
 *
 * This lifecycle ensures your app remains secure, predictable, and self-maintaining.
 */

use Avax\Filesystem\Storage\LocalFileStorage;
use Avax\HTTP\Session\Data\FileStore;
use Avax\HTTP\Session\Exceptions\SessionException;
use Avax\HTTP\Session\Features\Audit;
use Avax\HTTP\Session\Features\Events;
use Avax\HTTP\Session\Lifecycle\SessionProvider;
use Avax\HTTP\Session\Security\Policies\PolicyGroupBuilder;
use Avax\HTTP\Session\Security\SessionRegistry;

// -------------------------------------------------------------
// 1️⃣ SETUP — Preparing the environment
// -------------------------------------------------------------

/**
 * The FileStore handles where session data physically lives.
 * For example, here it writes serialized files to /storage/sessions/.
 */
$filesystem = new LocalFileStorage();
$store      = new FileStore(
    storage  : $filesystem,
    directory: __DIR__ . '/storage/sessions'
);

$audit    = new Audit(logPath: __DIR__ . '/logs/lifecycle_audit.log');
$events   = new Events();
$session  = new SessionProvider(store: $store);
$registry = new SessionRegistry(store: $store);

$session->registerFeature(feature: $audit);
$session->registerFeature(feature: $events);

// -------------------------------------------------------------
// 2️⃣ SESSION BIRTH — “Creating a new life”
// -------------------------------------------------------------
/**
 * 🧠 Concept:
 * A session is created when a user logs in or starts a new visit.
 * It receives a unique ID and empty storage.
 *
 * 💬 In human terms:
 * Think of this as giving every user their own locker in a gym 🏋️.
 * They can now safely store personal items (data).
 */

$session->start();
$userId = 42;
$session->put(key: 'user_id', value: $userId);
$audit->record(event: 'session_created', data: ['user_id' => $userId]);

echo "👶 Session started for user {$userId} with ID: {$session->getId()}\n";

// -------------------------------------------------------------
// 3️⃣ SESSION ACTIVITY — “Growing and changing”
// -------------------------------------------------------------
/**
 * As the user interacts with your app, you store or update data.
 * This represents an active, living session.
 *
 * Each write updates metadata — like last activity time.
 * This will matter later for idle timeout checks.
 */

$session->put(key: 'cart', value: ['item' => 'Laptop', 'price' => 1499]);
$audit->record(event: 'cart_update', data: ['item' => 'Laptop']);
echo "🧠 User added item to cart.\n";

// -------------------------------------------------------------
// 4️⃣ SESSION REGENERATION — “Changing identity for safety”
// -------------------------------------------------------------
/**
 * 🧠 Concept:
 * Session fixation attacks happen when an attacker tricks a victim
 * into using a known session ID. To prevent this, we *regenerate*
 * the ID after login or sensitive actions.
 *
 * 💬 Think of it as changing your house locks 🔑 after someone gets a spare key.
 * The contents stay the same — but the key (session ID) changes.
 *
 * ⚔️ Prevents: Session Fixation Attack
 */

$oldId = $session->getId();
$session->regenerateId();
$newId = $session->getId();

$audit->record(event: 'session_regenerated', data: ['old_id' => $oldId, 'new_id' => $newId]);
echo sprintf("♻️ Session ID regenerated (old: %s → new: %s)\n", $oldId, $newId);

// -------------------------------------------------------------
// 5️⃣ SESSION RENEWAL — “Extending an active life”
// -------------------------------------------------------------
/**
 * 🧠 Concept:
 * If a user stays active, we can “renew” their session
 * — meaning we extend its lifetime instead of expiring it.
 *
 * 💬 Real-world example:
 * Think of a parking ticket ⏱️.
 * If you keep feeding the meter (activity), your time extends.
 *
 * ⚙️ This works with MaxLifetimePolicy and MaxIdlePolicy.
 */

$policies = PolicyGroupBuilder::create()
    ->requireAll()
    ->maxIdle(seconds: 900)        // logout if idle 15 min
    ->maxLifetime(seconds: 3600)   // total life = 1 hour
    ->endGroup()
    ->build();

$session->registerPolicies(policies: [$policies]);
$session->renew();

$audit->record(event: 'session_renewed', data: ['user_id' => $userId]);
echo "🕒 Session lifetime extended — user is still active.\n";

// -------------------------------------------------------------
// 6️⃣ SESSION EXPIRATION — “Natural death”
// -------------------------------------------------------------
/**
 * 🧠 Concept:
 * If a user leaves your app and goes idle too long,
 * MaxIdlePolicy automatically marks the session as expired.
 *
 * 💬 Analogy:
 * Like your online banking session — if you go make coffee ☕,
 * it logs you out after 10 minutes of inactivity.
 */

sleep(seconds: 1); // Simulate time passing
try {
    $session->applyPolicy($policies);
    echo "✅ Session is still valid.\n";
} catch (SessionException $e) {
    echo "💀 Session expired: {$e->getMessage()}\n";
    $session->destroy();
}

// -------------------------------------------------------------
// 7️⃣ SESSION TERMINATION — “A clean and respectful goodbye”
// -------------------------------------------------------------
/**
 * 🧠 Concept:
 * Termination happens when the user explicitly logs out.
 * This wipes session data, revokes cookies, and updates the registry.
 *
 * 💬 Think of it as returning your gym locker key 🔐 and taking your stuff home.
 */

$session->destroy();
$audit->record(event: 'session_destroyed', data: ['user_id' => $userId]);
echo "👋 User {$userId} logged out — session destroyed.\n";

// -------------------------------------------------------------
// 8️⃣ SESSION REGISTRY — “Tracking multiple lives”
// -------------------------------------------------------------
/**
 * 🧠 Concept:
 * The Session Registry keeps track of all user sessions
 * across devices, browsers, or regions.
 *
 * 💬 Think of it like Netflix → "You are logged in on: Chrome, iPhone, Smart TV"
 *
 * You can:
 * - revoke sessions on other devices
 * - inspect all active logins
 * - detect hijacking attempts
 */

// Register two devices for the same user
$registry->register(userId: (string) $userId, sessionId: $newId, metadata: ['user_agent' => 'Chrome on macOS']);
$registry->register(userId: (string) $userId, sessionId: 'XYZ987', metadata: ['user_agent' => 'iPhone Safari']);
$audit->record(event: 'registry_update', data: ['active_devices' => 2]);

echo "📋 Active sessions for user {$userId}:\n";
foreach ($registry->getSessionsByDevice(userId: (string) $userId) as $device => $sessions) {
    foreach ($sessions as $data) {
        echo " - {$device} → {$data['session_id']}\n";
    }
}

// Simulate revoking one session (like user logs out on iPhone)
$registry->revoke(sessionId: 'XYZ987', reason: 'User manually logged out');
$audit->record(event: 'registry_revoke', data: ['device' => 'iPhone Safari']);
echo "🚫 Revoked iPhone session for user {$userId}.\n";

/**
 * -------------------------------------------------------------
 * 🧾 RECAP:
 * -------------------------------------------------------------
 * ✅ start() — new session born
 * ✅ put() — data written
 * ✅ regenerateId() — changed identity to prevent fixation
 * ✅ renew() — extended lifetime for active users
 * ✅ destroy() — end of life, all data wiped
 * ✅ registry — manage multiple “lives” across devices
 *
 * 💬 Think of it as:
 * "Your sessions now have a complete life cycle —
 * they live, evolve, and die safely on their own."
 */

echo "\n🌍 Session Lifecycle demo completed successfully.\n";
