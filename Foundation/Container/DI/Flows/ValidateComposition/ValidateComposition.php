<?php

declare(strict_types=1);

namespace Avax\Container\DI\Flows\ValidateComposition;

use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;

/**
 * Public validation flow for graph and policy checks.
 */
final readonly class ValidateComposition
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    /**
     * @param list<string> $serviceIds
     * @param array<string, mixed> $context
     * @return list<string>
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
