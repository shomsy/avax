<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Capabilities\UserSource;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Tests\TestCase;
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
