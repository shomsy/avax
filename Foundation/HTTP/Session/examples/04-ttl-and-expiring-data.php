<?php

/**
 * Example 04: TTL & Auto‑Expiring Data
 *
 * 🧠 Theory:
 * Think of TTL (Time To Live) like a small timer ⏱️ attached to a piece of data.
 * When the timer runs out, the data disappears automatically.
 *
 * Without TTL, everything you put into the session stays there
 * until the session ends or you remove it manually.
 *
 * That is fine for some things (like a user’s name),
 * but dangerous or noisy for others (like one‑time codes, OTPs, magic links).
 *
 * TTL lets you store temporary data in a safe way:
 * - One‑time login codes
 * - Password reset tokens
 * - Email verification links
 * - Short‑lived “remember this only for a few minutes” flags
 *
 * 🛡️ Real‑world scenario:
 * Imagine you send a user a 6‑digit code by SMS for login.
 * You want that code to work only for a short time (e.g. 5 minutes).
 *
 * With TTL you can say:
 * - “Store this code, but delete it automatically after 300 seconds.”
 * - Even if you forget to clean it up, the system does it for you.
 *
 * ⚙️ In practice (what we do here):
 * - We store an OTP code with a TTL of 300 seconds (5 minutes).
 * - We try to read it before and after the TTL window.
 * - We see that after the TTL passes, the value is gone (null).
 *
 * ✅ Result:
 * You learn how to attach a “self‑destruct timer” to session values,
 * so sensitive or temporary data does not live longer than it should.
 *
 * 📘 Vocabulary:
 * - TTL (Time To Live): how long a value is allowed to exist before it is removed.
 * - OTP (One‑Time Password): a short code used once and then discarded.
 * - Expiry: the moment when data becomes invalid and is removed.
 * - Window: the time period during which the data is still valid.
 */

use Avax\HTTP\Session\Session;

require __DIR__ . '/bootstrap.php';

/** @var Session $session */
$session = $sessionComponent; // from bootstrap, shared Session instance

$session->start();

echo "=== Example 04: TTL & Auto‑Expiring Data ===\n\n";

// Let's say we generate a one‑time code for login:
$otp = '739214';

// 🧠 Explanation of the chain:
//
// Think of `$session` as the place where we temporarily remember this code.
//
// `for('auth')`
//   Think of this like a small box labeled "auth".
//   We keep all authentication‑related data in this box.
//
// `ttl(300)`
//   Means: "keep this value for 300 seconds (5 minutes), then delete it".
//   Think of it like a sticker that says: "throw this away in 5 minutes".
//
// `put('otp', $otp)`
//   Means: "store the OTP code under the name otp inside this box".
//
// ✅ Result:
//   We store a one‑time code that automatically disappears after 5 minutes.

$session
    ->for('auth')
    ->ttl(300)
    ->put('otp', $otp);

echo "Stored OTP in session (valid for 5 minutes): {$otp}\n";

// Immediately after storing, we can read it:
$currentOtp = $session->for('auth')->get('otp');
echo "Current OTP from session: " . var_export($currentOtp, true) . "\n";

// In a real app, some time passes here (user types the code, submits form…)
// For the sake of example, imagine this is a later request:

// 🧠 Check if OTP still exists before validating:
//
// If get('otp') returns null, it probably expired or was already used.
$otpFromSession = $session->for('auth')->get('otp');

if ($otpFromSession === null) {
    echo "OTP is no longer available (it may have expired or been used).\n";
} else {
    echo "OTP is still valid: {$otpFromSession}\n";
}

// In your real verification logic, you would compare user input with $otpFromSession
// and, on success, you would typically remove it:
//
//   $session->for('auth')->forget('otp');
//
// Even if you forget to call forget(), TTL will clean it up after 300 seconds.
