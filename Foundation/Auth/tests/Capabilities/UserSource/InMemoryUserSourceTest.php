<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\UserSource;

use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flows\Login\Credentials;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the in-memory user source.
 */
class InMemoryUserSourceTest extends TestCase
{
    public function testFindByCredentialsMatchesUsernameCaseInsensitively() : void
    {
        $source = new InMemoryUserSource();
        $user   = User::create(
            id          : new UserId(value: 123),
            email       : new UserEmail(value: 'user@example.com'),
            username    : 'MiXeDUser',
            passwordHash: 'hash'
        );

        $source->create(user: $user);

        $found = $source->findByCredentials(credentials: new Credentials(
                                                             identifier: 'mixeduser',
                                                             password  : 'secret'
                                                         ));

        $this->assertSame(expected: $user, actual: $found);
    }
}
