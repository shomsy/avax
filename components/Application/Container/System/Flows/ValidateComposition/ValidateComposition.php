<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\ValidateComposition;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ServiceResolver;
use ReflectionException;

/**
 * Public validation flow for graph and policy checks.
 */
final readonly class ValidateComposition
{
    private ServiceResolver $resolver;

    public function __construct(
        ServiceResolver $resolver
    )
    {
        $this->resolver = $resolver;
    }

    /**
     * @param list<string>         $serviceIds
     * @param array<string, mixed> $context
     *
     * @return list<string>
     * @throws ReflectionException
     */
    public function validate(array|null $serviceIds = null, array $context = []) : array
    {
        $serviceIds ??= [];
        if ($context === []) {
            return $this->resolver->validate(serviceIds: $serviceIds);
        }

        return $this->resolver->validateInContext(serviceIds: $serviceIds, context: $context);
    }
}
