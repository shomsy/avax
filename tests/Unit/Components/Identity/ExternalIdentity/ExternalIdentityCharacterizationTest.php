<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\ExternalIdentity;

use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink\InMemoryExternalIdentityLinkStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityRuntime\ExternalIdentityRuntime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExternalIdentityCharacterizationTest extends TestCase
{
    private const USER_ID = 'user-1';
    private const PROVIDER = 'google';

    private ExternalIdentity $externalIdentity;

    #[Override]
    protected function setUp(): void
    {
        $this->externalIdentity = $this->externalIdentity();
    }

    #[Test]
    public function resolveReturnsNullForUnknownLink(): void
    {
        self::assertNull($this->externalIdentity->resolve(self::USER_ID, self::PROVIDER));
    }

    #[Test]
    public function linkAndResolveRoundTrip(): void
    {
        $data = ['external_id' => 'goog-123', 'email' => 'user@gmail.com'];
        $this->externalIdentity->link(self::USER_ID, self::PROVIDER, $data);

        $result = $this->externalIdentity->resolve(self::USER_ID, self::PROVIDER);
        self::assertSame($data, $result);
    }

    #[Test]
    public function sameUserDifferentProviders(): void
    {
        $this->externalIdentity->link(self::USER_ID, 'google', ['id' => 'g1']);
        $this->externalIdentity->link(self::USER_ID, 'github', ['id' => 'gh1']);

        self::assertSame(['id' => 'g1'], $this->externalIdentity->resolve(self::USER_ID, 'google'));
        self::assertSame(['id' => 'gh1'], $this->externalIdentity->resolve(self::USER_ID, 'github'));
    }

    #[Test]
    public function differentUsersSameProvider(): void
    {
        $this->externalIdentity->link('user-a', self::PROVIDER, ['id' => 'a']);
        $this->externalIdentity->link('user-b', self::PROVIDER, ['id' => 'b']);

        $resultA = $this->externalIdentity->resolve('user-a', self::PROVIDER);
        $resultB = $this->externalIdentity->resolve('user-b', self::PROVIDER);
        self::assertSame(['id' => 'a'], $resultA);
        self::assertSame(['id' => 'b'], $resultB);
    }

    #[Test]
    public function runtimeStoreIsReplaceableAtAssemblyBoundary(): void
    {
        $externalIdentity = $this->externalIdentity();

        $externalIdentity->link(self::USER_ID, 'custom', ['via' => 'store']);
        self::assertSame(['via' => 'store'], $externalIdentity->resolve(self::USER_ID, 'custom'));
    }

    #[Test]
    public function separateRuntimesDoNotShareExternalIdentityLinks(): void
    {
        $first = $this->externalIdentity();
        $second = $this->externalIdentity();

        $first->link(self::USER_ID, self::PROVIDER, ['id' => 'first']);

        self::assertSame(['id' => 'first'], $first->resolve(self::USER_ID, self::PROVIDER));
        self::assertNull($second->resolve(self::USER_ID, self::PROVIDER));
    }

    private function externalIdentity() : ExternalIdentity
    {
        return new ExternalIdentity(
            runtime: new ExternalIdentityRuntime(
                linkStore : new InMemoryExternalIdentityLinkStore(),
                oauth     : new \Avax\Components\Identity\ExternalIdentity\System\PublicSurface\OAuth(),
                oidc      : new \Avax\Components\Identity\ExternalIdentity\System\PublicSurface\Oidc(),
                federation: new \Avax\Components\Identity\ExternalIdentity\System\PublicSurface\Federation(),
            ),
        );
    }
}
