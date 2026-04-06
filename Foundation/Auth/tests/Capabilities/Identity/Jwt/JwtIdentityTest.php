<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Identity\Jwt;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserEmail;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Mockery;

/**
 * Unit test for JWT identity state handling.
 */
class JwtIdentityTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testJwtIdentityIssueAuthenticateAndClearCycle() : void
    {
        $user = new User(
            id: new UserId(7),
            email: new UserEmail('jwt@example.com'),
            username: 'jwt-user',
            passwordHash: 'hash'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')
            ->once()
            ->with(Mockery::on(fn($id) => $id instanceof UserId && $id->value === 7))
            ->andReturn($user);

        $jwt = new JwtIdentity(
            userSource: $userSource,
            secret: 'super-secret-key'
        );

        $token = $jwt->issue($user);

        $this->assertIsString($token);
        $this->assertTrue($jwt->check());
        $this->assertSame($user, $jwt->getCurrentUser());

        $jwt->clear();

        $this->assertFalse($jwt->check());
        $this->assertNull($jwt->getCurrentUser());

        $jwt->authenticate($token);

        $this->assertTrue($jwt->check());
        $this->assertSame($user, $jwt->getCurrentUser());
    }
}
