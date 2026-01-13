<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Inject\Contracts;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Inject\Resolvers\PropertyResolution;
use Avax\Container\Features\Think\Model\PropertyPrototype;

/**
 * Interface for PropertyInjector.
 *
 * @see docs/Features/Actions/Inject/Contracts/PropertyInjectorInterface.md#quick-summary
 */
interface PropertyInjectorInterface
{
    /**
     * Resolves an individual injectable property.
     *
     *
     * @see docs/Features/Actions/Inject/Contracts/PropertyInjectorInterface.md#method-resolve
     */
    public function resolve(
        PropertyPrototype $property,
        array             $overrides,
        KernelContext     $context,
        string            $ownerClass
    ) : PropertyResolution;
}
