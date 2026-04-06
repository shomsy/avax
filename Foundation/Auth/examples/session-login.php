<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Avax\Auth\Auth;
use Avax\Auth\Login\Credentials;
use Avax\Auth\Login\AuthFailed;

// 1. Setup your data source (e.g., Eloquent, Doctrine, or custom)
// $userSource = new MyUserSource();

// 2. Setup session identity
// $sessionIdentity = new SessionIdentity();

// 3. Create Auth instance
/*
$auth = Auth::createSession(
    userSource: $userSource,
    sessionIdentity: $sessionIdentity
);
*/

// 4. Handle Login
try {
    /*
    $user = $auth->login(new Credentials(
        identifier: 'user@example.com',
        password: 'password'
    ));
    echo "Welcome, " . $user->getUsername();
    */
} catch (AuthFailed $e) {
    echo "Login failed: " . $e->getMessage();
}

// 5. Check Auth status
/*
if ($auth->check()) {
    $currentUser = $auth->user();
}
*/
