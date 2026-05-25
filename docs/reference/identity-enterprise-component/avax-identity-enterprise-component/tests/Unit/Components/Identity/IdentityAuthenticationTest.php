<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity;

use Avax\Components\Identity\Capabilities\Accounts\UserAccount;
use Avax\Components\Identity\Capabilities\Passwords\PasswordCredential;
use Avax\Components\Identity\Capabilities\Passwords\NativePasswordHashing;
use Avax\Components\Identity\Configuration\CreateIdentityRuntimeGraph;
use Avax\Components\Identity\Configuration\IdentityConfiguration;
use Avax\Components\Identity\Flows\AuthenticatePassword\PasswordLogin;
use Avax\Components\Identity\Foundation\Values\LoginName;
use Avax\Components\Identity\Foundation\Values\PlainPassword;
use Avax\Components\Identity\Foundation\Values\TokenSecret;
use Avax\Components\Identity\Foundation\Values\UserId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityAuthenticationTest extends TestCase
{
    #[Test]
    public function password_authentication_accepts_valid_credentials(): void
    {
        $graph = (new CreateIdentityRuntimeGraph())->create(new IdentityConfiguration(TokenSecret::fromString(str_repeat('a', 32))));
        $userId = UserId::fromString('user-1');
        $login = LoginName::fromString('milos@example.com');
        $password = PlainPassword::fromSensitiveString('correct-password');
        $hasher = new NativePasswordHashing();

        $accounts = $this->readGraphProperty($graph->authentication()->authenticatePassword(), 'accounts');
        $credentials = $this->readGraphProperty($graph->authentication()->authenticatePassword(), 'credentials');

        $accounts->save(new UserAccount($userId, $login));
        $credentials->save(new PasswordCredential($userId, $hasher->hash($password)));

        $result = $graph->authentication()->authenticatePassword()->authenticate(new PasswordLogin($login, $password));

        self::assertTrue($result->isAuthenticated());
        self::assertSame('user-1', $result->userId()?->toString());
    }

    private function readGraphProperty(object $object, string $property): object
    {
        $reflection = new \ReflectionProperty($object, $property);

        return $reflection->getValue($object);
    }
}
