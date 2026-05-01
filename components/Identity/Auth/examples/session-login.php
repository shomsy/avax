<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Avax\Components\Identity\Auth\System\Auth;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Security\System\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;

$passwordHasher = new PasswordHasher();
$userSource     = new InMemoryUserSource();

$userSource->create(user: User::create(
    id          : new UserId(value: 1),
    email       : new UserEmail(value: 'user@example.com'),
    username    : 'user',
    passwordHash: $passwordHasher->hash(password: 'password'),
));

$auth = Auth::configuration()
    ->forUser(userSource: $userSource)
    ->withIdentityBackends(sessionIdentity: new SessionIdentity())
    ->usingHasher(passwordHasher: $passwordHasher)
    ->ready();

try {
    $loginResult = $auth->login(credentials: new Credentials(
                                                 identifier: 'user@example.com',
                                                 password  : 'password',
                                             ));

    echo 'Welcome, ' . $loginResult->user()->email() . PHP_EOL;
} catch (AuthenticationFailed $exception) {
    echo 'Login failed: ' . $exception->getMessage() . PHP_EOL;
}

if ($auth->check()) {
    $currentUser = $auth->user();

    if ($currentUser !== null) {
        echo 'Current user: ' . $currentUser->email() . PHP_EOL;
    }
}

$auth->logout();
