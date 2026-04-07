<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\UserSource;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserEmail;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flows\Login\Credentials;

/**
 * Unit test for the in-memory user source.
 */
class InMemoryUserSourceTest extends TestCase
{
    public function testFindByCredentialsMatchesUsernameCaseInsensitively() : void
    {
        $source = new InMemoryUserSource();
        $user = User::create(
            id: new UserId(123),
            email: new UserEmail('user@example.com'),
            username: 'MiXeDUser',
            passwordHash: 'hash'
        );

        $source->create($user);

        $found = $source->findByCredentials(new Credentials(
            identifier: 'mixeduser',
            password: 'secret'
        ));

        $this->assertSame($user, $found);
    }
}
