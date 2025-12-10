<?php
/**
 * Example 05: Flash Messages & Validation
 *
 * 🧠 Theory:
 * Flash messages are like sticky notes between two pages of your app 📄.
 *
 * They carry short pieces of information — like success, warnings, or errors —
 * from one request to the next, and then disappear automatically.
 *
 * Think of them as little messengers that deliver a message once,
 * then vanish forever 🕊️.
 *
 * 💡 Why use Flash?
 * When your app redirects (like after submitting a form),
 * the next page doesn’t know what happened before.
 * Flash messages help your app “remember just enough” to show:
 * - validation errors,
 * - confirmation messages,
 * - old input values.
 *
 * 🛠️ Flash message methods:
 * - `success()` → show a “yay!” message for successful actions
 * - `error()` → show what went wrong (like validation errors)
 * - `info()` → neutral notes or status
 * - `warning()` → gentle alerts or cautions
 * - `add()` → generic “set a custom flash key”
 * - `now()` → show message immediately (same request)
 * - `get()` → read & delete message
 *
 * 🧩 Real-world scenario:
 * Imagine a user submits a form but forgets to fill something.
 * You validate it, find errors, and redirect back to the form.
 *
 * With Flash:
 * - You can store the validation errors and user’s old input temporarily.
 * - On the next request, you show those messages in the view.
 *
 * 💬 Think of it like:
 * “I’ll write a quick note for the next page to read,
 * then throw it away once it’s seen.”
 */

use Avax\HTTP\Session\Session;

// Get the session instance (e.g. via DI container or singleton)
$session = Session::getInstance();

// ---------------------------------------------------------------------------
// 🧠 1. FORM VALIDATION FAILS
// ---------------------------------------------------------------------------
//
// Let’s simulate a failed form submission — for example,
// the user forgot to enter an email or used a weak password.
//
$errors = [
    'email'    => 'Email is required.',
    'password' => 'Password must be at least 8 characters.',
];

// 💡 Think of add('errors', $errors) like writing a small envelope
// and putting all error messages inside — it will be delivered
// to the *next* request.
//
$session->flash()->add('errors', $errors);

// 💬 Similarly, we can flash the old input,
// so the form can refill itself automatically.
//
$session->flash()->add('old_input', $_POST);

// 🧾 We can also flash a success-style message for later:
$session->flash()->success('Your profile has been updated successfully.');

// 💬 And maybe show something immediately (this request only):
$session->flash()->now('info', 'You are currently editing your profile.');

// ---------------------------------------------------------------------------
// 🔄 2. NEXT REQUEST (AFTER REDIRECT)
// ---------------------------------------------------------------------------
//
// Now imagine the user is redirected back to the form.
// On the next page load, the view can retrieve what was flashed.
//
$errors   = $session->flash()->get('errors', []);
$oldInput = $session->flash()->get('old_input', []);

// 💬 Think of get('errors') like opening that envelope from your past self.
// Once you read it, the envelope disappears — no duplicates, no stale data.
//
$successMessage = $session->flash()->get('success');

// ---------------------------------------------------------------------------
// 🧩 3. SHOWING FLASH DATA IN THE VIEW
// ---------------------------------------------------------------------------
//
// This part would normally live in your view layer (e.g., Twig or Blade).
// We’ll just simulate it with simple echo statements.
//
if ($successMessage) {
    echo "<div class='alert alert-success'>{$successMessage}</div>";
}

if ($errors) {
    echo "<div class='alert alert-danger'>";
    echo "<strong>Validation failed:</strong><br>";
    foreach ($errors as $field => $message) {
        echo "- {$field}: {$message}<br>";
    }
    echo "</div>";
}

// 💡 Think of it like this:
// The “success” note was for the next page only.
// The “errors” and “old_input” were carried here,
// then automatically erased once shown.
//
// On the next refresh, all of these will be gone — clean slate! ✨

// ---------------------------------------------------------------------------
// 🧹 4. CLEARING FLASH
// ---------------------------------------------------------------------------
//
// You can also manually clear all flash data if needed.
//
$session->flash()->clear();

// ---------------------------------------------------------------------------
// ✅ RESULT
// ---------------------------------------------------------------------------
//
// - Flash is your app’s one-request memory.
// - “add()” → set temporary data for the next page.
// - “success()/error()/info()/warning()” → quick helpers.
// - “get()” → read and delete.
// - “now()” → show immediately (this request only).
// - “clear()” → erase all flash data.
//
// 💡 Think of Flash as the app whispering to itself between pages:
// “Just remember this message until tomorrow — then forget it.” 🌙
//
