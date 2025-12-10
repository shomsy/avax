<?php

/**
 * Example 01: Basic Operations & Lifecycle
 *
 * 🧠 Theory:
 * Think of a session like a small notebook that your app keeps for each user.
 * In that notebook you can write things you want to remember between requests:
 * their name, their cart items, their preferences.
 *
 * Without a session, every HTTP request is “new” and “forgetful”.
 * The server does not remember who you are from one page to the next.
 *
 * A session fixes that.
 * It gives you a safe place to store small pieces of data per user.
 *
 * 🛡️ Real-world scenario:
 * - A user logs in once, but stays logged in across many pages.
 * - A user adds items to a shopping cart and sees them on the checkout page.
 * - A user changes a setting (dark mode on), and it stays that way.
 *
 * The session is the memory that makes all of this possible.
 *
 * ⚙️ In practice (what we do here):
 * - We start the session (open the notebook for this user).
 * - We write values: put data into the notebook.
 * - We read values: get data back later.
 * - We check if data exists.
 * - We remove data or clear everything when we are done.
 *
 * ✅ Result:
 * You will see how to:
 * - remember a user's name,
 * - update it,
 * - forget it,
 * - and finally destroy the whole session when it is no longer needed.
 *
 * 📘 Vocabulary:
 * - Session: the user’s personal notebook that the server keeps.
 * - Start: opening the notebook for this user.
 * - ID: a secret “notebook number” that tells which notebook belongs to which user.
 * - Put: writing something into the notebook.
 * - Get: reading something from the notebook.
 * - Forget: erasing one note from the notebook.
 * - Flush: erasing everything in the notebook.
 */

use Avax\HTTP\Session\Session;

require __DIR__ . '/bootstrap.php';

/**
 * Note: This example uses the actual Session API
 * as defined in SessionInterface / SessionContract
 * (start, id, put, get, has, forget, flush, destroy).
 * It is intended to be copy-pasteable against the
 * real implementation, not pseudo-code.
 */
/** @var Session $session */
$session = $sessionComponent; // from bootstrap, shared Session instance

// 🧠 Step 1: Start the session lifecycle
//
// Think of this as: “open the notebook for this user”.
// If the notebook is already open, this call simply ensures it is ready.
$session->start();

// Get the current session ID (the notebook number).
$sessionId = $session->id();

echo "Session started with ID: {$sessionId}\n\n";

// 🧠 Step 2: Basic put() and get()
//
// Think of [put('name', 'Alice')](cci:1://file:///C:/Users/shomsy/PhpstormProjects/components/Foundation/HTTP/Session/Session.php:69:4-81:5) like writing “name = Alice” into the notebook.
// Later you can ask: “what is name?” using get('name').

// Store a simple value.
$session->put('name', 'Alice');

// Read it back.
$name = $session->get('name');

echo "Stored name: {$name}\n"; // Alice

// 🧠 Step 3: has() — does this note exist?
//
// [has('name')](cci:1://file:///C:/Users/shomsy/PhpstormProjects/components/Foundation/HTTP/Session/Session.php:96:4-106:5) answers: “Does the notebook contain a value called name?”
// It returns true or false.
if ($session->has('name')) {
    echo "We have a name stored in the session.\n";
}

// 🧠 Step 4: Updating values
//
// Calling put() again with the same key simply overwrites the old value.
// Think of it like crossing out the old value and writing a new one.

$session->put('name', 'Bob');
echo "Updated name: " . $session->get('name') . "\n"; // Bob

// 🧠 Step 5: forget() — remove a single value
//
// [forget('name')](cci:1://file:///C:/Users/shomsy/PhpstormProjects/components/Foundation/HTTP/Session/Session.php:108:4-118:5) means: erase just this one line from the notebook.
// Other notes stay untouched.

$session->forget('name');

echo "After forget('name'):\n";
var_dump([
    'has_name' => $session->has('name'),   // false
    'name'     => $session->get('name'),   // null
]);

// 🧠 Step 6: put multiple values
//
// You can treat the session like a small key-value store
// for different pieces of user data.

$session->put('user_id', 42);
$session->put('role', 'admin');
$session->put('theme', 'dark');

echo "\nCurrent session data snapshot:\n";
var_dump([
    'user_id' => $session->get('user_id'),
    'role'    => $session->get('role'),
    'theme'   => $session->get('theme'),
]);

// 🧠 Step 7: flush() — clear everything
//
// [flush()](cci:1://file:///C:/Users/shomsy/PhpstormProjects/components/Foundation/HTTP/Session/Session.php:130:4-138:5) means: erase the whole notebook for this user.
// After this, it is as if the user never had a session.

$session->flush();

echo "\nAfter flush():\n";
var_dump([
    'user_id' => $session->get('user_id'), // null
    'role'    => $session->get('role'),    // null
    'theme'   => $session->get('theme'),   // null
]);

// 🧠 Step 8: destroy() — end the lifecycle
//
// Think of `destroy()` as:
//  - close the notebook,
//  - throw it away,
//  - tell the browser that this session is no longer valid.
//
// This is often used on logout.

$session->destroy();

echo "\nSession destroyed. ID is now: " . $session->id() . "\n";
// Depending on your implementation, this may be an empty string
// or a fresh ID after regeneration.

