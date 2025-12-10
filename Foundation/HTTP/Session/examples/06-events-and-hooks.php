<?php
/**
 * Example 06: Events & Hooks (Simplified for Everyone)
 *
 * 🧠 Theory:
 * When something happens inside the Session system (like saving or deleting data),
 * it can “shout” that information to anyone who wants to listen.
 *
 * These shouts are called “events”.
 * The people listening are called “listeners”.
 *
 * Imagine your Session is a person in a busy kitchen 🍳:
 * - When they finish cooking (store data), they shout “Dinner’s ready!”
 * - When they clean the table (delete data), they shout “Table cleared!”
 * Other cooks (listeners) can then react, like:
 * - One writes the meal to a logbook (“Audit”)
 * - Another counts how many meals were made today (“Metrics”)
 *
 * Events are not just for fun — they make your Session *observable*.
 * You can track, log, and respond to everything that happens.
 */

use Avax\HTTP\Session\Session;

// Get the Session instance
$session = Session::getInstance();

/**
 * === Step 1: Listening to events ===
 *
 * “Listen” means: do something when this event happens.
 */

$session->events()->listen('stored', function (array $data) {
    echo "[Event: stored] You saved '{$data['key']}' in your session.\n";
});

$session->events()->listen('deleted', function (array $data) {
    echo "[Event: deleted] You deleted '{$data['key']}' from your session.\n";
});

// === Step 2: Triggering events ===
// Each of these will automatically "shout" (dispatch) an event.
$session->put('username', 'John'); // triggers “stored”
$session->forget('username');      // triggers “deleted”


/**
 * === Step 3: Understanding setMode() ===
 *
 * Normally, events happen immediately (synchronously).
 * That means — when you save something, the event runs right away.
 *
 * But sometimes, you don’t want to slow things down.
 * For example, logging or analytics can wait until after the page loads.
 *
 * That’s where `setMode()` comes in!
 *
 * It changes *when and how* your events are delivered.
 *
 * 🧩 Think of it like sending a message:
 * - SYNC: You call your friend and talk right now.
 * - ASYNC_MEMORY: You leave them a sticky note — they’ll read it later.
 * - ASYNC_FILE: You write it down in a notebook — they’ll read it tomorrow.
 * - ASYNC_REDIS: You post it to a group chat — everyone will get it instantly.
 *
 * So, if your event just logs something to a file or database,
 * it’s usually better to run it *asynchronously* (in the background)
 * so your user doesn’t have to wait.
 */

// 🧠 Example: make events async (run later)
$session->events()->setMode('ASYNC_MEMORY');

$session->events()->listen('stored', function (array $data) {
    echo "[ASYNC Event: stored] I'll write '{$data['key']}' to a log file *after* the page loads.\n";
});

$session->put('settings', ['theme' => 'dark']); // event queued, not run immediately!

echo "Page is rendering...\n";

/**
 * 🔍 Output explanation:
 * You’ll first see: “Page is rendering...”
 * Then, after the script finishes, PHP will run your async listeners.
 * So the “[ASYNC Event: stored] ...” message appears *after* the response ends.
 *
 * 💡 Why? Because async events use a shutdown handler —
 * they collect events in memory and process them when PHP is done.
 *
 * This is great for performance:
 * - The user doesn’t wait for logs or analytics
 * - The app feels faster
 * - You can handle thousands of events without slowing anything down
 *
 * 🛠️ Tip:
 * In production, you might switch to:
 *   - ASYNC_FILE → if you want logs written to disk
 *   - ASYNC_REDIS → if you want events shared between multiple servers
 *
 * For example:
 *   $session->events()->setMode('ASYNC_REDIS');
 *   // Now all servers see the same events in real time
 */


/**
 * === Step 4: Combine everything together ===
 *
 * You can mix sync and async listeners freely:
 * - Sync → for critical stuff (security, policy enforcement)
 * - Async → for optional stuff (analytics, audit)
 *
 * Example:
 */
$session->events()->setMode('SYNC'); // switch back to instant mode

$session->events()->listen('stored', function (array $data) {
    echo "[SECURITY] Immediately verified '{$data['key']}' integrity.\n";
});

$session->events()->setMode('ASYNC_MEMORY'); // back to async for logs
$session->events()->listen('stored', function (array $data) {
    echo "[LOG] Queued '{$data['key']}' for audit logging.\n";
});

$session->put('user_id', 101);


/**
 * 🧠 Summary:
 * - setMode('SYNC') → run listeners right away (instant reaction)
 * - setMode('ASYNC_MEMORY') → run them later (after response ends)
 * - setMode('ASYNC_FILE') → save events to disk (persistent queue)
 * - setMode('ASYNC_REDIS') → send to Redis (shared queue for multi-server apps)
 *
 * 💬 In simple words:
 * setMode() controls *when* the session “tells” its story.
 * You decide if it should speak now, whisper later, or write a note for others.
 */
