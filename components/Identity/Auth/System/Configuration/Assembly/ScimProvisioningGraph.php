<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Assembly;

use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentityStoreInterface;
use SensitiveParameter;

/**
 * Configuration assembly graph for SCIM provisioning-related dependencies.
 *
 * Owns: SCIM directory stores, provisioned identity stores,
 * lifecycle store, and SCIM throttle.
 */
final class ScimProvisioningGraph
{
    private ScimDirectoryStoreInterface|null $scimDirectoryStore = null;
    private ScimProvisionedIdentityStoreInterface|null $scimProvisionedIdentityStore = null;
    private LifecycleStoreInterface|null $lifecycleStore = null;
    private AttemptThrottle|null $scimThrottle = null;

    public function withScimDirectoryStore(ScimDirectoryStoreInterface $scimDirectoryStore) : self
    {
        $this->scimDirectoryStore = $scimDirectoryStore;

        return $this;
    }

    public function withScimProvisionedIdentityStore(ScimProvisionedIdentityStoreInterface $scimProvisionedIdentityStore) : self
    {
        $this->scimProvisionedIdentityStore = $scimProvisionedIdentityStore;

        return $this;
    }

    public function withLifecycleStore(#[SensitiveParameter] LifecycleStoreInterface $lifecycleStore) : self
    {
        $this->lifecycleStore = $lifecycleStore;

        return $this;
    }

    public function withScimThrottle(AttemptThrottle $attemptThrottle) : self
    {
        $this->scimThrottle = $attemptThrottle;

        return $this;
    }

    /**
     * @return array{
     *     scimDirectoryStore: ScimDirectoryStoreInterface|null,
     *     scimProvisionedIdentityStore: ScimProvisionedIdentityStoreInterface|null,
     *     lifecycleStore: LifecycleStoreInterface|null,
     *     scimThrottle: AttemptThrottle|null,
     * }
     */
    public function assemble() : array
    {
        return [
            'scimDirectoryStore'           => $this->scimDirectoryStore,
            'scimProvisionedIdentityStore' => $this->scimProvisionedIdentityStore,
            'lifecycleStore'               => $this->lifecycleStore,
            'scimThrottle'                 => $this->scimThrottle,
        ];
    }
}
