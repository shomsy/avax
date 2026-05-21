<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\ExternalIdentity;

use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExternalIdentityCharacterizationTest extends TestCase
{
    private const USER_ID = 'user-1';
    private const PROVIDER = 'google';

    #[Override]
    protected function setUp(): void
    {
        // Reset to fresh store to ensure test isolation
        $ref = new \ReflectionProperty(ExternalIdentity::class, 'linkStore');
        $ref->setValue(null, null);
    }

    #[Test]
    public function resolveReturnsNullForUnknownLink(): void
    {
        self::assertNull(ExternalIdentity::resolve(self::USER_ID, self::PROVIDER));
    }

    #[Test]
    public function linkAndResolveRoundTrip(): void
    {
        $data = ['external_id' => 'goog-123', 'email' => 'user@gmail.com'];
        ExternalIdentity::link(self::USER_ID, self::PROVIDER, $data);

        $result = ExternalIdentity::resolve(self::USER_ID, self::PROVIDER);
        self::assertSame($data, $result);
    }

    #[Test]
    public function sameUserDifferentProviders(): void
    {
        ExternalIdentity::link(self::USER_ID, 'google', ['id' => 'g1']);
        ExternalIdentity::link(self::USER_ID, 'github', ['id' => 'gh1']);

        self::assertSame(['id' => 'g1'], ExternalIdentity::resolve(self::USER_ID, 'google'));
        self::assertSame(['id' => 'gh1'], ExternalIdentity::resolve(self::USER_ID, 'github'));
    }

    #[Test]
    public function differentUsersSameProvider(): void
    {
        ExternalIdentity::link('user-a', self::PROVIDER, ['id' => 'a']);
        ExternalIdentity::link('user-b', self::PROVIDER, ['id' => 'b']);

        $resultA = ExternalIdentity::resolve('user-a', self::PROVIDER);
        $resultB = ExternalIdentity::resolve('user-b', self::PROVIDER);
        self::assertSame(['id' => 'a'], $resultA);
        self::assertSame(['id' => 'b'], $resultB);
    }

    #[Test]
    public function backwardCompatWithReplaceableStore(): void
    {
        $customStore = new \Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink\InMemoryExternalIdentityLinkStore();
        ExternalIdentity::setLinkStore($customStore);

        ExternalIdentity::link(self::USER_ID, 'custom', ['via' => 'store']);
        self::assertSame(['via' => 'store'], ExternalIdentity::resolve(self::USER_ID, 'custom'));
    }
}
