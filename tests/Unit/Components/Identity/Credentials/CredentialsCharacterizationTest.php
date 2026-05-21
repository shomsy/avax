<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Credentials;

use Avax\Components\Identity\Credentials\System\PublicSurface\Credentials;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CredentialsCharacterizationTest extends TestCase
{
    private const USER_ID = 'user-1';

    #[Override]
    protected function tearDown(): void
    {
        Credentials::forget(self::USER_ID);
        Credentials::forget('other-user');
    }

    #[Test]
    public function storeAndReadRoundTrip(): void
    {
        $data = ['password_hash' => 'abc123', 'method' => 'bcrypt'];
        Credentials::store(self::USER_ID, $data);

        $result = Credentials::read(self::USER_ID);
        self::assertSame($data, $result);
    }

    #[Test]
    public function readReturnsNullForUnknownUser(): void
    {
        self::assertNull(Credentials::read('no-such-user'));
    }

    #[Test]
    public function forgetRemovesStoredCredentials(): void
    {
        Credentials::store(self::USER_ID, ['key' => 'value']);
        Credentials::forget(self::USER_ID);

        self::assertNull(Credentials::read(self::USER_ID));
    }

    #[Test]
    public function storeOverwritesExistingEntry(): void
    {
        Credentials::store(self::USER_ID, ['old' => 'data']);
        Credentials::store(self::USER_ID, ['new' => 'data']);

        self::assertSame(['new' => 'data'], Credentials::read(self::USER_ID));
    }

    #[Test]
    public function multipleUsersDoNotInterfere(): void
    {
        Credentials::store(self::USER_ID, ['a' => 1]);
        Credentials::store('other-user', ['b' => 2]);

        self::assertSame(['a' => 1], Credentials::read(self::USER_ID));
        self::assertSame(['b' => 2], Credentials::read('other-user'));
    }

    #[Test]
    public function allMethodsAreStatic(): void
    {
        $reflection = new \ReflectionClass(Credentials::class);
        foreach ($reflection->getMethods() as $method) {
            self::assertTrue(
                $method->isStatic(),
                "Credentials::{$method->getName()}() should be static",
            );
        }
    }

    #[Test]
    public function storeAcceptsArbitraryCredentialArrays(): void
    {
        Credentials::store(self::USER_ID, ['type' => 'password', 'hash' => 'xyz', 'salt' => 'nacl']);
        $result = Credentials::read(self::USER_ID);
        self::assertNotNull($result);
        self::assertSame('password', $result['type']);
        self::assertSame('xyz', $result['hash']);
    }
}
