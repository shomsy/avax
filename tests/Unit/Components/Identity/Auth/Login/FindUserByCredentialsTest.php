<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Flows\Login\FindUserByCredentials;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FindUserByCredentialsTest extends TestCase
{
    #[Test]
    public function test_it_returns_user_when_email_exists(): void
    {
        $user = new User(
            id: new UserId(1),
            email: new UserEmail('milos@example.com'),
            username: 'milos',
            passwordHash: 'hashed',
        );

        $userSource = new InMemoryUserSource();
        $userSource->create($user);

        $findUser = new FindUserByCredentials($userSource);

        $result = $findUser->execute('milos@example.com');

        self::assertInstanceOf(User::class, $result);
        self::assertSame('milos@example.com', $result->email->value);
    }

    #[Test]
    public function test_it_returns_null_when_email_does_not_exist(): void
    {
        $userSource = new InMemoryUserSource();

        $findUser = new FindUserByCredentials($userSource);

        $result = $findUser->execute('nonexistent@example.com');

        self::assertNull($result);
    }
}
