<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use components\Auth\System\Auth;
use components\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use components\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use components\Auth\System\Capabilities\Identity\User\User;
use components\Auth\System\Capabilities\Identity\User\UserEmail;
use components\Auth\System\Capabilities\Identity\User\UserId;
use components\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use components\Auth\System\Flows\Login\AuthenticationFailed;
use components\Auth\System\Flows\Login\Credentials;

$passwordHasher = new PasswordHasher();
$userSource     = new InMemoryUserSource();

$userSource->create(user: User::create(
    id          : new UserId(value: 1),
    email       : new UserEmail(value: 'user@example.com'),
    username    : 'user',
    passwordHash: $passwordHasher->hash(password: 'password')
));

$auth = Auth::configuration()
    ->forUser(userSource: $userSource)
    ->withIdentityBackends(sessionIdentity: new SessionIdentity())
    ->usingHasher(passwordHasher: $passwordHasher)
    ->ready();

try {
    $loginResult = $auth->login(credentials: new Credentials(
                                                 identifier: 'user@example.com',
                                                 password  : 'password'
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
