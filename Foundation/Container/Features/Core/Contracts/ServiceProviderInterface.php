<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Contracts;

use Avax\Container\Container;

/**
 * Service provider contract for deterministic registration.
 *
 * @see docs/Features/Core/Contracts/ServiceProviderInterface.md#quick-summary
 */
interface ServiceProviderInterface
{
    public function __construct(Container $app);

    public function register() : void;

    public function boot() : void;
}
