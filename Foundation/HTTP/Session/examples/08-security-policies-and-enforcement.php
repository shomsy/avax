<?php
/**
 * Example 08: Security Policies & Enforcement
 *
 * 🧠 Theory:
 * Security Policies are like *rules that guard your session*.
 * Each one watches over a specific type of risk and decides
 * whether the session should continue or be stopped.
 *
 * 💬 Think of them as “security guards” standing outside your session:
 * - One checks if you’ve been gone too long (MaxIdlePolicy)
 * - One ensures you came through the secure HTTPS door (SecureOnlyPolicy)
 * - One verifies your ticket hasn’t expired (MaxLifetimePolicy)
 * - One checks you’re on the same network (SessionIpPolicy)
 * - One makes sure it’s really your device (CrossAgentPolicy)
 * - And one manager makes sure all guards work together (CompositePolicy)
 *
 * 🛡️ Why this matters:
 * Without these guards, sessions could stay open forever,
 * even on public computers, or travel over unsafe networks.
 *
 * ---------------------------------------------------------------------
 * 🔒 Every Policy has a single job:
 *   → To check ONE condition and either approve or reject the session.
 *
 * If it fails, it throws a PolicyViolationException —
 * which instantly ends or invalidates your session.
 * ---------------------------------------------------------------------
 *  🧠 Enforcement
 *
 *  "Enforcement" means *actively applying* all security policies
 *  before allowing any session action.
 *
 *  Policies define the rules — enforcement ensures those rules
 *  are actually respected.
 *
 *  Think of it like a security guard at the door:
 *  - Policies = The building's rules
 *  - Enforcement = The guard checking everyone who enters
 *
 *  Without enforcement, policies are just words on paper.
 */

use Avax\HTTP\Session\Exceptions\PolicyViolationException;
use Avax\HTTP\Session\Security\Policies\{CompositePolicy,
    CrossAgentPolicy,
    MaxIdlePolicy,
    MaxLifetimePolicy,
    PolicyGroupBuilder,
    SecureOnlyPolicy,
    SessionIpPolicy};
use Avax\HTTP\Session\Session;

/**
 * Note: All policies used below (`MaxIdlePolicy`, `MaxLifetimePolicy`,
 * `SecureOnlyPolicy`, `SessionIpPolicy`, `CrossAgentPolicy`,
 * `CompositePolicy`, `PolicyGroupBuilder`) are real classes from the
 * session engine with the exact constructor signatures shown here.
 * This example is meant to be executable against the actual codebase.
 */

$session = Session::getInstance();

/**
 * -------------------------------------------------------------
 * 🕒 1. MaxIdlePolicy — “The inactivity guard”
 * -------------------------------------------------------------
 *
 * 🧠 What it does:
 * Checks how long a user has been inactive.
 * If too much time passes (say, 15 minutes), the session expires.
 *
 * 💬 Think of it like:
 * You’re at a coffee shop using free Wi-Fi ☕.
 * You walk away for too long, and the Wi-Fi disconnects — because it
 * assumes you’re gone. It’s polite security, not punishment.
 *
 * 💡 Why it exists:
 * - Prevents hijacking of forgotten sessions
 * - Logs out users who leave sessions open
 *
 * 🏦 Real-world analogy:
 * Like an ATM 🏧 — if you stop pressing buttons, it cancels your session.
 */

$maxIdle = new MaxIdlePolicy(maxIdleSeconds: 15 * 60); // 15 minutes

/**
 * -------------------------------------------------------------
 * ⏳ 2. MaxLifetimePolicy — “The absolute expiry guard”
 * -------------------------------------------------------------
 *
 * 🧠 What it does:
 * This one says, “No matter how active you are, your session dies
 * after a fixed total time — for example, 2 hours.”
 *
 * 💬 Think of it like:
 * A parking ticket ⏱️ — you can move your car around, but after
 * 2 hours the ticket expires no matter what.
 *
 * 💡 Why it exists:
 * - Limits total lifetime of any session token
 * - Prevents attackers from reusing long-lived sessions
 *
 * 🏢 Real-world analogy:
 * A concert wristband 🎟️ that stops being valid after midnight.
 */

/**
 * ⚠️ NOTE: Don’t confuse MaxLifetimePolicy with TTL.
 *
 * These two sound similar but control different things:
 *
 * - 🕒 TTL (Time-To-Live) applies to a single key/value.
 *   → Example: $session->put('otp', '123456', ttl: 300);
 *     Only this OTP will expire after 5 minutes.
 *
 * - ⏳ MaxLifetimePolicy applies to the entire session.
 *   → Example: new MaxLifetimePolicy(3600);
 *     The whole session (all keys) will expire after 1 hour,
 *     even if the user keeps using it.
 *
 * 💡 Think of it like:
 * - TTL = “The milk in your fridge has an expiration date.” 🥛
 * - MaxLifetimePolicy = “The whole fridge turns off at midnight.” 🕛
 *
 * Use both for maximum safety:
 * TTL for short-lived sensitive data (like OTPs or tokens),
 * MaxLifetimePolicy for overall session expiration.
 */
$maxLifetime = new MaxLifetimePolicy(maxLifetimeSeconds: 2 * 3600); // 2 hours total lifetime

/**
 * -------------------------------------------------------------
 * 🔒 3. SecureOnlyPolicy — “The HTTPS guard”
 * -------------------------------------------------------------
 *
 * 🧠 What it does:
 * Allows sessions only over HTTPS connections.
 *
 * 💬 Think of it like:
 * A private conversation in a soundproof room 🔇 —
 * if you’re trying to talk through a megaphone in public (HTTP),
 * this guard slams the door shut.
 *
 * 💡 Why it exists:
 * - Stops attackers from stealing cookies via sniffing
 * - Ensures your data travels through an encrypted tunnel
 *
 * 🧍 Real-world analogy:
 * Like whispering secrets in someone’s ear instead of shouting in the street.
 */

$secureOnly = new SecureOnlyPolicy(true);

/**
 * -------------------------------------------------------------
 * 🌐 4. SessionIpPolicy — “The network guard”
 * -------------------------------------------------------------
 *
 * 🧠 What it does:
 * Ties your session to the same IP address it started from.
 * If your IP suddenly changes (for example, from one city to another),
 * it assumes something suspicious happened.
 *
 * 💬 Think of it like:
 * You checked into a hotel under your name 🏨.
 * If someone tries to use your room key from another building,
 * the hotel system locks it immediately.
 *
 * 💡 Why it exists:
 * - Prevents hijacking when session IDs are stolen
 * - Ensures sessions can’t jump between networks
 *
 * 🕵️ Real-world analogy:
 * Like a keycard that only works at one hotel door — not anywhere else.
 */

$ipPolicy = new SessionIpPolicy();

/**
 * -------------------------------------------------------------
 * 🧭 5. CrossAgentPolicy — “The device fingerprint guard”
 * -------------------------------------------------------------
 *
 * 🧠 What it does:
 * Locks your session to the same browser or device that created it.
 * If you log in with Chrome and someone tries to use your session in Safari,
 * it gets blocked.
 *
 * 💬 Think of it like:
 * Your face unlock feature on a phone 📱 — it won’t open for anyone else.
 *
 * 💡 Why it exists:
 * - Stops session reuse from stolen cookies
 * - Ensures the session only works on your original device
 *
 * 🧩 Real-world analogy:
 * It’s like a theater ticket 🎭 that says “valid only on this seat and this show”.
 */

$crossAgent = new CrossAgentPolicy();

/**
 * -------------------------------------------------------------
 * 🧩 6. CompositePolicy — “The team manager”
 * -------------------------------------------------------------
 *
 * 🧠 What it does:
 * Combines multiple smaller guards into one powerful security unit.
 *
 * 💬 Think of it like:
 * A night club with several bouncers 🕺:
 * - One checks your ID (SecureOnlyPolicy)
 * - One checks if you’ve been idle (MaxIdlePolicy)
 * - One checks your ticket hasn’t expired (MaxLifetimePolicy)
 *
 * If *any* says “no”, you’re not getting in.
 */

$compositePolicy = new CompositePolicy(policies: [
    $maxIdle,
    $maxLifetime,
    $secureOnly,
    $ipPolicy,
    $crossAgent
]);

/**
 * -------------------------------------------------------------
 * ⚙️ 7. Applying Policies to the Session
 * -------------------------------------------------------------
 *
 * Usually, a SessionManager / SessionProvider wires these policies
 * and runs them before operations like `get()`, `put()`, or
 * `regenerateId()`. In this example we call `$session->applyPolicy()`
 * directly only to make the enforcement flow explicit and easy to
 * follow in isolation.
 *
 * NOTE: In production, SessionProvider enforces these policies automatically.
 * You don't need to call applyPolicy() manually in your app code.
 */

try {
    $session->applyPolicy($compositePolicy);
    $session->put('user_id', 42);
    echo "✅ Session is secure and active.\n";

    // Simulate user being idle too long
    sleep(seconds: 16 * 60); // 16 minutes

    // This triggers MaxIdlePolicy
    $session->get('user_id');

} catch (PolicyViolationException $e) {
    echo "🚨 Policy violation: {$e->getMessage()}\n";
}

/**
 * -------------------------------------------------------------
 * 🧱 8. PolicyGroupBuilder — “The recipe maker”
 * -------------------------------------------------------------
 *
 * 🧠 What it does:
 * Lets you build a policy group with readable, fluent syntax.
 *
 * 💬 Think of it like:
 * Writing your own “security recipe” in plain English.
 *
 * Example:
 * “All sessions must be secure, expire after 15 minutes, and lock to device.”
 *
 * Note: `PolicyGroupBuilder::create()->requireAll()->secureOnly()
 * ->maxIdle(900)->maxLifetime(3600)->ipBinding()->userAgentBinding()`
 * is the real fluent API from the policy subsystem, not pseudo-code.
 */

$securityRecipe = PolicyGroupBuilder::create()
    ->requireAll()
    ->secureOnly()
    ->maxIdle(seconds: 900)
    ->maxLifetime(seconds: 3600)
    ->ipBinding()
    ->userAgentBinding()
    ->endGroup()
    ->build();

$session->applyPolicy($securityRecipe);

echo "✅ Built and applied security recipe successfully.\n";

/**
 * -------------------------------------------------------------
 * 🧠 9. How policies actually work inside the engine
 * -------------------------------------------------------------
 *
 * - Each policy implements `PolicyInterface` → has `check(Session $session): void`
 * - The session engine runs `check()` for every active policy before any action
 * - If any fails → throws `PolicyViolationException`
 *
 * 🧩 Simplified internal logic:
 *
 * class MaxIdlePolicy implements PolicyInterface {
 *     public function check(Session $session): void {
 *         $lastActivity = $session->getMeta('last_activity');
 *         if (time() - $lastActivity > $this->maxIdleTime) {
 *             throw new PolicyViolationException('Session expired due to inactivity.');
 *         }
 *     }
 * }
 *
 * 💡 You never call `check()` manually — the framework does it for you.
 */

/**
 * -------------------------------------------------------------
 * 🧩 10. Best Practices & Real-world Mapping
 * -------------------------------------------------------------
 *
 * 🛡️ Always include at least:
 * - MaxIdlePolicy → ends idle sessions
 * - SecureOnlyPolicy → forces HTTPS
 *
 * 🧩 Add for extra safety:
 * - SessionIpPolicy → ties session to the same IP
 * - CrossAgentPolicy → ties session to the same device
 * - MaxLifetimePolicy → expires sessions after total time
 *
 * 🏢 Real-world examples:
 * - 💳 Online banking: auto-logout after 10 minutes (MaxIdle)
 * - ⚙️ Admin dashboards: HTTPS only (SecureOnly)
 * - 🧑‍💻 Corporate VPNs: IP bound sessions (SessionIp)
 * - 📱 Mobile apps: device-bound sessions (CrossAgent)
 *
 * 🔍 Advanced tip:
 * Combine these with your Audit feature to log all violations.
 * You’ll get a live feed of suspicious behavior.
 */

echo "\n✅ Security Policies & Enforcement example completed successfully.\n";

/**
 * -------------------------------------------------------------
 * 🧾 Recap
 * -------------------------------------------------------------
 * - MaxIdlePolicy → Like an ATM timeout — closes inactive sessions
 * - MaxLifetimePolicy → Like a parking ticket — expires after total time
 * - SecureOnlyPolicy → Like whispering secrets in private (HTTPS)
 * - SessionIpPolicy → Like a hotel key that works only for one room
 * - CrossAgentPolicy → Like Face ID — only works for your own device
 * - CompositePolicy → Like a team of guards working together
 * - PolicyGroupBuilder → Like writing a “security recipe”
 *
 * 🧠 Together they form your session’s immune system.
 * Your app automatically enforces good behavior — no extra work needed.
 *
 * 💬 Think of it as:
 * “Your sessions now protect themselves.”
 */
