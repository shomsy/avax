<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\CheckAuthentication;

use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use SensitiveParameter;

/**
 * Check if there is an active authentication state.
 *
 * Banal: Screaming flow name.
 */
final readonly class CheckAuthentication
{
    public function __construct(
        #[SensitiveParameter] private IdentityInterface $identity
    ) {}

    public function execute() : bool
    {
        return $this->identity->check();
    }
}
