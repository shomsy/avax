<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Avax\Auth\System\Auth;
use Avax\Auth\System\AuthBuilder;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Login\AuthFailed;
use Avax\Auth\System\Flow\Login\Credentials;

// Example UserSource (replace with your PDO/Eloquent/etc.)
class ExampleUserSource implements UserSourceInterface {
    public function findByCredentials(string $identifier, #[\SensitiveParameter] string $password): object|null
    {
        // Simulate DB lookup + password verify
        if ($identifier === 'user@example.com' && hash('sha256', $password) === hash('sha256', 'password')) {
            return (object)['id' => '1', 'email' => $identifier];
        }
        return null;
    }
}

// 1. Setup UserSource and SessionIdentity (e.g., your session handler)
$userSource = new ExampleUserSource();
$sessionIdentity = new SessionIdentity(); // Your session storage

// 2. Create Auth instance
$auth = AuthBuilder::create()
    ->withUserSource($userSource)
    ->withSessionIdentity($sessionIdentity)
    ->build();

// 3. Handle Login
try {
    $loginResult = $auth->login(new Credentials(
        identifier: 'user@example.com',
        password: 'password'
    ));
    echo "Welcome, " . $loginResult->user()->email();
} catch (AuthFailed $e) {
    echo "Login failed: " . $e->getMessage();
}

// 4. Check Auth status
if ($auth->check()) {
    $currentUser = $auth->user();
    echo "Current user: " . $currentUser->email();
}

// 5. Logout
$auth->logout();

