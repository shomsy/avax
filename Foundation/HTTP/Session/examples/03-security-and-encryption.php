<?php

/**
 * Example 03: Security & Encryption
 *
 * 🧠 Theory:
 * Think of your session like a small backpack that your app carries for each user.
 * Inside that backpack you can put important things: tokens, IDs, preferences.
 *
 * But there is a problem:
 * If someone steals the backpack, they can see everything inside.
 * That means they could pretend to be the user, or read secret data.
 *
 * Encryption is like putting everything inside a locked box
 * before you put it into the backpack.
 * The box can only be opened with a secret key that only your app knows.
 *
 * So even if an attacker steals the backpack,
 * all they see is a locked box with random-looking data.
 * They cannot read it, they cannot change it in a useful way.
 *
 * 🛡️ Real-world scenario:
 * Imagine a user logs into an online banking app.
 * The app stores a session token that proves "this user is authenticated".
 * If that token is stored in plain text and someone steals it,
 * they can log in as that user without knowing the password.
 *
 * With encryption:
 * - The token is stored in an encrypted form.
 * - Even if someone dumps the session storage or intercepts the data,
 *   they only see garbage they cannot use.
 *
 * ⚙️ In practice (what we do here):
 * - We configure the Session Component with encryption enabled.
 * - We tell the Session: "for this area, always encrypt data before saving".
 * - We store a token with a time limit, so it auto-expires after 15 minutes.
 *
 * ✅ Result:
 * Your app can safely store sensitive values (tokens, emails, IDs)
 * without exposing them in plain text.
 * Even if the storage (filesystem, Redis, database) is leaked,
 * the attacker cannot simply read or reuse the data.
 *
 * 📘 Vocabulary:
 * - Encryption: turning readable data into unreadable text using a secret key.
 * - Decryption: turning that unreadable text back into the original value.
 * - Secret key: a private value only your app knows, used to lock/unlock data.
 * - Token: a small piece of data that proves "who you are" to the app.
 * - TTL (Time To Live): how long data is allowed to live before it disappears.
 */

use Avax\HTTP\Session\Session;

require __DIR__ . '/bootstrap.php';

/** @var Session $session */
$session = $sessionComponent; // from bootstrap, shared Session instance

$jwt = 'header.payload.signature'; // pretend this is a real JWT from your auth system

// 🧠 Explanation of the chain below:
//
// Think of `$session` like a helper that knows how to store data safely.
//
// `for('auth')`
//   Think of this like saving data in a box called "auth".
//   All login/authentication data goes into this one box,
//   so it does not mix with other things like "cart" or "preferences".
//
// `secure()`
//   This means: "Before you save anything in this box, lock it."
//   The data will be encrypted using a secret key.
//   Even if someone opens the storage, they only see random characters,
//   not the real value.
//
// `ttl(900)`
//   TTL means "time to live".
//   `ttl(900)` means: keep this data for 900 seconds (15 minutes), then delete it.
//   Think of it like a message that self-destructs after 15 minutes.
//   This is perfect for short-lived tokens.
//
// `put('token', $jwt)`
//   This means: "Save a value under the name token."
//   Here `$jwt` is your login or access token.
//   Combined with `secure()` and `ttl(900)`,
//   it becomes an encrypted, temporary token.
//
// ✅ Result:
//   The user's token is:
//   - stored only inside the "auth" area,
//   - encrypted so nobody can read it from raw storage,
//   - automatically removed after 15 minutes.
//

$session
    ->for('auth')
    ->secure()
    ->ttl(900)
    ->put('token', $jwt);

// Later in the request, or in the next request, you can read it back:
//
// Think of `get('token')` like opening the "auth" box
// and asking: "Do we still have the token for this user?"

$token = $session->for('auth')->get('token');

var_dump([
    'stored_token_value' => $token,
]);
