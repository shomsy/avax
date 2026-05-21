<?php

declare(strict_types=1);

require_once __DIR__.'/../../vendor/autoload.php';

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\NullAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\CorrelatingAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionLifetime;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\NativeSessionStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Configuration\Builders\AuthBuilder;
use Avax\Components\Identity\Auth\System\Configuration\AuthServiceProvider;
use Avax\Components\Identity\Auth\System\Configuration\Builders\RegisterAuthDefaults;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\IdGenerator;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;

$passwordHasher = new PasswordHasher();
$userSource = new InMemoryUserSource();

$userSource->create(user: User::create(
    username    : 'user',
    passwordHash: $passwordHasher->hash(password: 'password'),
    id          : new UserId(value: 1),
    email       : new UserEmail(value: 'user@example.com'),
));

// Set up container with AuthServiceProvider defaults
$engine = new SimpleContainer();
$serviceProvider = new AuthServiceProvider();
$serviceProvider->register($engine);

$clock = new Clock();
$auditLog = new NullAuditLog();

$auth = (new AuthBuilder())
    ->withClock($clock)
    ->usingIdGenerator(new IdGenerator())
    ->usingHasher($passwordHasher)
    ->withAuditLog($auditLog)
    ->forUser(userSource: $userSource)
    ->withIdentityBackends(sessionIdentity: new SessionIdentity(
                                                    sessionStore   : new NativeSessionStore(),
                                                    clock          : $clock,
                                                    auditLog       : $auditLog,
                                                    sessionLifetime: new SessionLifetime(),
                                                ))
    ->ready();

try {
    $loginResult = $auth->login(credentials: new Credentials(
        identifier: 'user@example.com',
        password  : 'password',
    ));

    echo 'Welcome, '.$loginResult->user()->email().PHP_EOL;
} catch (AuthenticationFailed $authenticationFailed) {
    echo 'Login failed: '.$authenticationFailed->getMessage().PHP_EOL;
}

if ($auth->check()) {
    $currentUser = $auth->user();

    if ($currentUser !== null) {
        echo 'Current user: '.$currentUser->email().PHP_EOL;
    }
}

$auth->logout();
