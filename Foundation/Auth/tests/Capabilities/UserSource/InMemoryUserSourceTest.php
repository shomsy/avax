<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\UserSource;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Login\Credentials;

/**
 * Unit test for the in-memory user source.
 */
class InMemoryUserSourceTest extends TestCase
{
    public function testFindByCredentialsMatchesUsernameCaseInsensitively() : void
    {
        $source = new InMemoryUserSource();
        $user = User::create(
            id: new UserId(value: 123),
            email: new UserEmail(value: 'user@example.com'),
            username: 'MiXeDUser',
            passwordHash: 'hash'
        );

        $source->create(user: $user);

        $found = $source->findByCredentials(credentials: new Credentials(
            identifier: 'mixeduser',
            password: 'secret'
        ));

        $this->assertSame(expected: $user, actual: $found);
    }
}
