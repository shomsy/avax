<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\PublicSurface;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink\ExternalIdentityLinkStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink\InMemoryExternalIdentityLinkStore;

/**
 * ExternalIdentity — manages external identity links (OAuth, SSO, etc.).
 *
 * @deprecated Inject ExternalIdentityLinkStoreInterface directly instead of using this static facade.
 *             Use ExternalIdentityGraph for assembly. This class remains for backward compatibility
 *             but the static link state is no longer owned directly by this class.
 */
final class ExternalIdentity
{
    private static ?ExternalIdentityLinkStoreInterface $linkStore = null;

    /**
     * Replace the backing store (for DI integration).
     */
    public static function setLinkStore(ExternalIdentityLinkStoreInterface $store) : void
    {
        self::$linkStore = $store;
    }

    /**
     * @param array<string, mixed> $externalData
     */
    public static function link(string $userId, string $provider, array $externalData) : void
    {
        self::resolveStore()->link($userId, $provider, $externalData);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function resolve(string $userId, string $provider) : array|null
    {
        return self::resolveStore()->resolve($userId, $provider);
    }

    private static function resolveStore() : ExternalIdentityLinkStoreInterface
    {
        if (self::$linkStore === null) {
            self::$linkStore = new InMemoryExternalIdentityLinkStore();
        }

        return self::$linkStore;
    }
}
