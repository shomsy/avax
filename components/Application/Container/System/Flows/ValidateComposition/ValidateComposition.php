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
    public function __construct(private ServiceResolver $serviceResolver)
    {
    }

    /**
     * @param list<string> $serviceIds
     * @param array<string, mixed> $context
     *
     * @return list<string>
     * @throws ReflectionException
     */
    public function validate(?array $serviceIds = null, array $context = []) : array
    {
        $serviceIds ??= [];
        if ($context === []) {
            return $this->serviceResolver->validate(serviceIds: $serviceIds);
        }

        return $this->serviceResolver->validateInContext(serviceIds: $serviceIds, context: $context);
    }
}
