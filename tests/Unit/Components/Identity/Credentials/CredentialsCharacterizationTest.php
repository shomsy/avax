<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Credentials;

use Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore\InMemoryCredentialStore;
use Avax\Components\Identity\Credentials\System\Configuration\Assembly\CredentialsGraph;
use Avax\Components\Identity\Credentials\System\PublicSurface\Credentials;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CredentialsCharacterizationTest extends TestCase
{
    private const USER_ID = 'user-1';

    private Credentials $credentials;

    #[Override]
    protected function setUp(): void
    {
        $this->credentials = $this->credentials();
    }

    #[Test]
    public function storeAndReadRoundTrip(): void
    {
        $data = ['password_hash' => 'abc123', 'method' => 'bcrypt'];
        $this->credentials->store(self::USER_ID, $data);

        $result = $this->credentials->read(self::USER_ID);
        self::assertSame($data, $result);
    }

    #[Test]
    public function readReturnsNullForUnknownUser(): void
    {
        self::assertNull($this->credentials->read('no-such-user'));
    }

    #[Test]
    public function forgetRemovesStoredCredentials(): void
    {
        $this->credentials->store(self::USER_ID, ['key' => 'value']);
        $this->credentials->forget(self::USER_ID);

        self::assertNull($this->credentials->read(self::USER_ID));
    }

    #[Test]
    public function storeOverwritesExistingEntry(): void
    {
        $this->credentials->store(self::USER_ID, ['old' => 'data']);
        $this->credentials->store(self::USER_ID, ['new' => 'data']);

        self::assertSame(['new' => 'data'], $this->credentials->read(self::USER_ID));
    }

    #[Test]
    public function multipleUsersDoNotInterfere(): void
    {
        $this->credentials->store(self::USER_ID, ['a' => 1]);
        $this->credentials->store('other-user', ['b' => 2]);

        self::assertSame(['a' => 1], $this->credentials->read(self::USER_ID));
        self::assertSame(['b' => 2], $this->credentials->read('other-user'));
    }

    #[Test]
    public function runtimeStoreIsReplaceableAtAssemblyBoundary(): void
    {
        $credentials = $this->credentials();

        $credentials->store(self::USER_ID, ['via' => 'custom']);
        self::assertSame(['via' => 'custom'], $credentials->read(self::USER_ID));
    }

    #[Test]
    public function storeAcceptsArbitraryCredentialArrays(): void
    {
        $this->credentials->store(self::USER_ID, ['type' => 'password', 'hash' => 'xyz', 'salt' => 'nacl']);
        $result = $this->credentials->read(self::USER_ID);
        self::assertNotNull($result);
        self::assertSame('password', $result['type']);
        self::assertSame('xyz', $result['hash']);
    }

    #[Test]
    public function separateRuntimesDoNotShareCredentials(): void
    {
        $first = $this->credentials();
        $second = $this->credentials();

        $first->store(self::USER_ID, ['id' => 'first']);

        self::assertSame(['id' => 'first'], $first->read(self::USER_ID));
        self::assertNull($second->read(self::USER_ID));
    }

    private function credentials() : Credentials
    {
        return CredentialsGraph::fromStore(store: new InMemoryCredentialStore());
    }
}
