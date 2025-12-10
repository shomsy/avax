<?php

/**
 * Example 02: Scopes & Namespaces (for())
 *
 * 🧠 Theory:
 * Think of your session like a big closet 🧳 that your app uses to store information.
 * Inside that closet, you can have many shelves — one for login data, one for a shopping cart,
 * one for user preferences, one for temporary messages.
 *
 * Each shelf has a name — and you decide what goes on it.
 *
 * The method `for('something')` is how you open one of those shelves.
 * It’s like saying: "Hey session, I want to work on the *auth shelf* now."
 *
 * That shelf becomes a *scope* — a small, private area inside the session where
 * all the keys you store live under that group.
 *
 * 💡 Why it matters:
 * Without scopes, all your session data gets mixed in one big messy drawer.
 * With scopes, you can keep things clean and separate — no name collisions,
 * no confusion between modules, and easier debugging.
 *
 * Example:
 * - `for('auth')->put('token', '123')` saves under: `auth.token`
 * - `for('cart')->put('items', [...])` saves under: `cart.items`
 *
 * Each `for()` call opens a *namespace* — a logical box inside the session.
 * It doesn’t create a new session, it just organizes your data.
 *
 * 🛡️ Real-world scenario:
 * Imagine your app has three parts:
 * - "auth" → handles login
 * - "cart" → handles shopping
 * - "ui"   → handles user preferences
 *
 * Each of these can store their own data in the same session safely:
 * - Auth can store token, roles, last login.
 * - Cart can store selected items, total, coupon.
 * - UI can store theme, language, layout.
 *
 * With scopes, they all live in the same closet — but on their own shelves.
 *
 * ⚙️ In practice:
 * - Use `for('auth')` when you work with login/session tokens.
 * - Use `for('cart')` for shopping data.
 * - Use `for('ui')` for user preferences.
 * - You can chain it with other features like `secure()` or `ttl()`.
 *
 * 📘 Vocabulary:
 * - Scope: A named section of the session, like a labeled box or shelf.
 * - Namespace: A prefix added to all keys inside a scope.
 * - Collision: When two parts of code accidentally use the same key.
 * - Isolation: Keeping different session data separate.
 * - Chaining: Combining multiple modifiers in one line (e.g. `for()->secure()->ttl()`).
 *
 * ✅ Result:
 * You get clean, organized, easy-to-manage session data.
 * Each part of your app keeps its own data on its own shelf — no mixing, no overwriting.
 */

use Avax\HTTP\Session\Session;

require __DIR__ . '/bootstrap.php';

/** @var Session $session */
$session = $sessionComponent; // from bootstrap, shared Session instance

$session->start();

echo "=== Example 02: Scopes & Namespaces ===\n\n";

// 🧠 Think of `$session` as the big closet where all your app’s memory lives.
//
// Each `for('something')` is like opening a labeled box inside that closet.
//
// Let’s create three different scopes (auth, cart, ui):
$auth = $session->for('auth');
$cart = $session->for('cart');
$ui   = $session->for('ui');

// ---------------------------------------------------------------------------
// 👛 AUTH SCOPE
// ---------------------------------------------------------------------------
//
// This shelf stores all authentication data.
//
// Think of it like a "login box" — only login-related data goes here.
//
$auth->put('token', 'user-token-abc123');
$auth->put('roles', ['user', 'editor']);
$auth->put('last_login', time());

// You can read them later:
$token = $auth->get('token');
echo "🔐 Token from auth scope: {$token}\n";

// ---------------------------------------------------------------------------
// 🛒 CART SCOPE
// ---------------------------------------------------------------------------
//
// This shelf stores shopping cart data.
//
// Think of it like a "basket box" — where all user’s items go.
//
$cart->put('items', ['T-shirt', 'Sneakers', 'Cap']);
$cart->put('total', 89.97);

// Read them later:
$items = implode(', ', $cart->get('items'));
$total = $cart->get('total');

echo "🛍️ Cart has: {$items} (Total: \\${$total})\n";

// ---------------------------------------------------------------------------
// 🎨 UI SCOPE
// ---------------------------------------------------------------------------
//
// This shelf stores user preferences (theme, language, etc.)
//
// Think of it like a "settings box" — where the app remembers how you like things.
//
$ui->put('theme', 'dark');
$ui->put('language', 'en');

echo "🎨 UI theme: {$ui->get('theme')} | Language: {$ui->get('language')}\n";

// ---------------------------------------------------------------------------
// 🧩 UNDERSTANDING WHAT HAPPENS INTERNALLY
// ---------------------------------------------------------------------------
//
// Each scope automatically prefixes its data.
// So if we looked inside the raw session storage, we’d see something like:
//
//   auth.token       => "user-token-abc123"
//   auth.roles       => ["user", "editor"]
//   auth.last_login  => 1702239123
//   cart.items       => ["T-shirt", "Sneakers", "Cap"]
//   cart.total       => 89.97
//   ui.theme         => "dark"
//   ui.language      => "en"
//
// 💡 Think of it like a JSON structure:
//
// {
//   "auth": { "token": "...", "roles": [...], "last_login": ... },
//   "cart": { "items": [...], "total": ... },
//   "ui":   { "theme": "dark", "language": "en" }
// }
//
// All organized. All separate. All neat.
//
// ---------------------------------------------------------------------------
// ⚙️ YOU CAN EVEN CHAIN OTHER FEATURES
// ---------------------------------------------------------------------------
//
// For example, let’s make our "auth" box secure and short-lived:
//
// 💬 Think of it like this:
// "This is my login shelf. Lock it with encryption and make it forget
// everything after 15 minutes."
//
$session
    ->for('auth')
    ->secure()
    ->ttl(900)
    ->put('token', 'secure-jwt-xyz');

// ---------------------------------------------------------------------------
// ✅ RESULT
// ---------------------------------------------------------------------------
//
// - "auth" shelf keeps login info (secure, temporary)
// - "cart" shelf keeps shopping data
// - "ui" shelf keeps design preferences
// - Everything is isolated and easy to manage
//
// 💡 Think of for('auth') like saying:
// "Open the login shelf — I only want to work there."
// 💡 Think of for('cart') like saying:
// "Now open the shopping shelf."
// 💡 Think of for('ui') like saying:
// "Open the preferences shelf."
//
// Each box keeps its own stuff.
// No more collisions. No more mess.
