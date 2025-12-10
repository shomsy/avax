<?php
/**
 * Example 07: Audit & Metrics
 *
 * 🧠 Theory:
 * The “Audit” and “Metrics” features help you **see and understand**
 * what’s really happening inside your Session system.
 *
 * Think of your session as a “black box” 📦 — you store, update, and delete data,
 * but without these tools, you’d have no idea *when*, *how often*, or *by whom*.
 *
 * - 🧾 **Audit** keeps a historical log of what happened (like a journal).
 * - 📊 **Metrics** counts and measures actions (like a dashboard).
 *
 * Together, they make your Session *observable* — you can monitor, detect issues,
 * measure performance, and catch unusual activity in real time.
 *
 * 💡 In simple words:
 * - Audit answers: “What happened?”
 * - Metrics answers: “How often did it happen?”
 * - Security answers: “Should it have happened?”
 *
 * These three work together to make your app reliable, measurable, and safe.
 */

use Avax\HTTP\Session\Features\Audit;
use Avax\HTTP\Session\Features\Metrics;
use Avax\HTTP\Session\Session;

// Get a session instance
$session = Session::getInstance();

/**
 * === Step 1: Initialize the Audit and Metrics features ===
 *
 * The Audit feature records every session event (store, delete, flush, destroy)
 * and saves it to a structured log file (usually JSON or line-based text).
 *
 * The Metrics feature counts how many times things happen.
 * Example: how many times we stored something, deleted something, etc.
 */

$audit   = new Audit('/tmp/session_audit.log');   // Save audit entries to this file
$metrics = new Metrics();                         // In-memory metrics tracker

// Register them to listen to session events
$session->events()->listen('*', [$audit, 'record']);
$session->events()->listen('*', [$metrics, 'increment']);

/**
 * 🧩 Think of this like adding two assistants to your Session:
 * - “Auditor” writes everything down in a notebook (who did what, and when)
 * - “Statistician” keeps a counter on the wall tallying how many times it happened
 */

// === Step 2: Perform normal session actions ===
$session->put('user_id', 42);
$session->put('cart_items', ['apple', 'banana']);
$session->forget('cart_items');
$session->flush();

/**
 * 🧠 What happens under the hood:
 * - Every `put()` triggers a `stored` event → Audit logs it, Metrics increments it.
 * - Every `forget()` triggers a `deleted` event → Audit logs it, Metrics increments it.
 * - Every `flush()` triggers a `cleared` event → both log and count it.
 *
 * 🧾 Example Audit log line (JSON):
 *   {"event":"stored","key":"user_id","time":"2025-12-05T18:24:00Z"}
 *
 * 📈 Example Metrics output:
 *   stored: 2
 *   deleted: 1
 *   cleared: 1
 */

// === Step 3: Review your audit logs ===
echo "\n--- Audit log content ---\n";
echo file_get_contents('/tmp/session_audit.log');

/**
 * 💬 Real-world uses of Audit:
 * - 🧑‍💼 Compliance — prove who accessed what (GDPR, SOC2)
 * - 🧠 Debugging — track unexpected session mutations
 * - 🔒 Security — detect abnormal access patterns or replay attacks
 *
 * For example:
 * - “Why did the user’s session reset at midnight?”
 * - “Who deleted all the keys from this namespace?”
 * - “Was this action done from a new IP?”
 */

// === Step 4: Check your metrics counters ===
echo "\n--- Metrics summary ---\n";
print_r($metrics->report());

/**
 * 💡 Real-world uses of Metrics:
 * - Track total session writes/deletes
 * - Monitor spikes in session churn (possible attack)
 * - Visualize active session load in Prometheus or Grafana
 *
 * For example:
 * - “We had 5k session stores/min → possible login storm”
 * - “Spike in session deletions → maybe session policy enforcement triggered”
 */

// === Step 5: Customizing audit format ===
/**
 * Audit logs can be customized:
 * You can choose to write JSON, plain text, or even send logs to an external system.
 *
 * Example of custom formatter:
 */
$audit->setFormatter(function (string $event, array $context) {
    $time = date('H:i:s');

    return "[{$time}] EVENT: {$event} | CONTEXT: " . json_encode($context) . PHP_EOL;
});

$session->put('debug_key', 'value');

/**
 * Output will now look like:
 * [18:45:02] EVENT: stored | CONTEXT: {"key":"debug_key"}
 *
 * This gives you total control over how your logs are written and read.
 */

// === Step 6: Combining Audit + Metrics + Events ===
/**
 * You can have all three features working together like a team:
 *
 * Events  → tell the story (who/what/when)
 * Audit   → writes it down (record of truth)
 * Metrics → counts it up (measure and visualize)
 *
 * This trio gives you 360° visibility into your application’s session activity.
 *
 * 💡 Tip:
 * You can ship these logs to tools like:
 * - ELK Stack (Elasticsearch + Logstash + Kibana)
 * - Grafana Loki
 * - Prometheus (via Metrics)
 * - Sentry or Bugsnag (for error traces)
 */

echo "\n✅ Session Audit & Metrics example completed.\n";

/**
 * 🧠 Summary:
 * - Audit keeps history of every change (what happened)
 * - Metrics keeps counters (how often it happened)
 * - Together, they make your session system transparent
 *
 * 🧩 Think of them as:
 *   - Audit = Journal 📘
 *   - Metrics = Dashboard 📊
 *   - Events = Nerve signals ⚡
 *
 * With these three, your Session component isn’t a “black box” anymore —
 * it’s an intelligent, traceable, and measurable system.
 */

/**
 * Example 07 (Extended): Audit & Metrics in a Real App
 *
 * 🧠 Real-world scenario:
 * Let’s imagine we’re building a real web app with login/logout.
 *
 * Each time a user logs in, we:
 *  - store their ID and name in the session
 *  - record the event in an audit log
 *  - increment our “active sessions” counter
 *
 * Each time a user logs out, we:
 *  - remove their session data
 *  - log the logout event
 *  - decrement the counter
 *
 * This way, we always know:
 *  - Who logged in / logged out
 *  - When they did it
 *  - How many users are active right now
 *
 * This is how large enterprise systems maintain traceability and transparency.
 */

$session = Session::getInstance();
$audit   = new Audit('/tmp/audit_realworld.log');
$metrics = new Metrics();

// register features to listen on every event
$session->events()->listen('*', [$audit, 'record']);
$session->events()->listen('*', [$metrics, 'increment']);

/**
 * === Step 1: Simulate a user login ===
 *
 * When a user logs in, we store session data.
 * Each of these will emit a `stored` event → automatically logged and counted.
 */

function loginUser(Session $session, string $userId, string $username) : void
{
    $session->put('user_id', $userId);
    $session->put('username', $username);
    echo "✅ User '{$username}' logged in.\n";
}

loginUser($session, 'U123', 'Alice');

/**
 * The Audit log will now have:
 *   {"event":"stored","key":"user_id",...}
 *   {"event":"stored","key":"username",...}
 *
 * And Metrics report will include:
 *   stored: 2
 */

/**
 * === Step 2: Simulate a page visit ===
 *
 * Even when users just browse around,
 * we can record “page_accessed” events manually.
 * This helps us measure engagement.
 */

$session->events()->dispatch('page_accessed', ['path' => '/dashboard', 'user_id' => 'U123']);

/**
 * === Step 3: Simulate a logout ===
 *
 * On logout, we remove all session data and
 * emit a “logout” event for audit & metrics tracking.
 */

function logoutUser(Session $session) : void
{
    $session->flush(); // clears everything, emits `cleared`
    $session->events()->dispatch('logout', ['reason' => 'user_requested']);
    echo "🚪 User logged out.\n";
}

logoutUser($session);

/**
 * === Step 4: Review the results ===
 */
echo "\n--- Audit log ---\n";
echo file_get_contents('/tmp/audit_realworld.log');

echo "\n--- Metrics summary ---\n";
print_r($metrics->report());

/**
 * 🧠 What did we get here?
 *
 * - Every important user action (login, browse, logout) was tracked.
 * - Audit provides *history* (who, what, when).
 * - Metrics provides *numbers* (how often it happened).
 *
 * 🧾 Audit snippet:
 * {"event":"stored","key":"user_id"}
 * {"event":"stored","key":"username"}
 * {"event":"logout","reason":"user_requested"}
 *
 * 📊 Metrics summary:
 * Array
 * (
 *   [stored] => 2
 *   [cleared] => 1
 *   [logout] => 1
 * )
 *
 * 🧩 Why this matters:
 * In production, this data helps you:
 * - Detect unusual activity (many logins per minute)
 * - Identify crashes (sessions that end too quickly)
 * - Prove compliance (who did what and when)
 *
 * 💡 Tip:
 * You could also visualize `$metrics->report()` in a live dashboard,
 * or send `$audit` logs to Elastic / Loki / Sentry for monitoring.
 */

echo "\n✅ Real-world Audit & Metrics demo completed.\n";

//  REAL WORLD EXAMPLE (continued)

/**
 * Example 07 (Extended): Audit & Metrics in a Real App
 *
 * 🧠 Real-world scenario:
 * Let’s imagine we’re building a real web app with login/logout.
 *
 * Each time a user logs in, we:
 *  - store their ID and name in the session
 *  - record the event in an audit log
 *  - increment our “active sessions” counter
 *
 * Each time a user logs out, we:
 *  - remove their session data
 *  - log the logout event
 *  - decrement the counter
 *
 * This way, we always know:
 *  - Who logged in / logged out
 *  - When they did it
 *  - How many users are active right now
 *
 * This is how large enterprise systems maintain traceability and transparency.
 */

$session = Session::getInstance();
$audit   = new Audit('/tmp/audit_realworld.log');
$metrics = new Metrics();

// register features to listen on every event
$session->events()->listen('*', [$audit, 'record']);
$session->events()->listen('*', [$metrics, 'increment']);

/**
 * === Step 1: Simulate a user login ===
 *
 * When a user logs in, we store session data.
 * Each of these will emit a `stored` event → automatically logged and counted.
 */

function loginUser(Session $session, string $userId, string $username) : void
{
    $session->put('user_id', $userId);
    $session->put('username', $username);
    echo "✅ User '{$username}' logged in.\n";
}

loginUser($session, 'U123', 'Alice');

/**
 * The Audit log will now have:
 *   {"event":"stored","key":"user_id",...}
 *   {"event":"stored","key":"username",...}
 *
 * And Metrics report will include:
 *   stored: 2
 */

/**
 * === Step 2: Simulate a page visit ===
 *
 * Even when users just browse around,
 * we can record “page_accessed” events manually.
 * This helps us measure engagement.
 */

$session->events()->dispatch('page_accessed', ['path' => '/dashboard', 'user_id' => 'U123']);

/**
 * === Step 3: Simulate a logout ===
 *
 * On logout, we remove all session data and
 * emit a “logout” event for audit & metrics tracking.
 */

function logoutUser(Session $session) : void
{
    $session->flush(); // clears everything, emits `cleared`
    $session->events()->dispatch('logout', ['reason' => 'user_requested']);
    echo "🚪 User logged out.\n";
}

logoutUser($session);

/**
 * === Step 4: Review the results ===
 */
echo "\n--- Audit log ---\n";
echo file_get_contents('/tmp/audit_realworld.log');

echo "\n--- Metrics summary ---\n";
print_r($metrics->report());

/**
 * 🧠 What did we get here?
 *
 * - Every important user action (login, browse, logout) was tracked.
 * - Audit provides *history* (who, what, when).
 * - Metrics provides *numbers* (how often it happened).
 *
 * 🧾 Audit snippet:
 * {"event":"stored","key":"user_id"}
 * {"event":"stored","key":"username"}
 * {"event":"logout","reason":"user_requested"}
 *
 * 📊 Metrics summary:
 * Array
 * (
 *   [stored] => 2
 *   [cleared] => 1
 *   [logout] => 1
 * )
 *
 * 🧩 Why this matters:
 * In production, this data helps you:
 * - Detect unusual activity (many logins per minute)
 * - Identify crashes (sessions that end too quickly)
 * - Prove compliance (who did what and when)
 *
 * 💡 Tip:
 * You could also visualize `$metrics->report()` in a live dashboard,
 * or send `$audit` logs to Elastic / Loki / Sentry for monitoring.
 */

echo "\n✅ Real-world Audit & Metrics demo completed.\n";
