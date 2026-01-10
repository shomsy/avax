<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Core\DTO\ErrorDTO;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Avax\Container\Guard\Enforce\GuardResolution;

/**
 * Guard Policy Step - Security and Policy Enforcement
 *
 * Enforces security policies and access controls before service resolution.
 * This step acts as a gatekeeper, validating that the requested service
 * can be resolved according to configured security rules and policies.
 *
 * @package Avax\Container\Core\Kernel\Steps
 * @see docs/Core/Kernel/Steps/GuardPolicyStep.md#quick-summary
 */
final readonly class GuardPolicyStep implements KernelStep
{
    /**
     * @param GuardResolution $guard Guard policy evaluator.
     * @see docs/Core/Kernel/Steps/GuardPolicyStep.md#method-__construct
     */
    public function __construct(
        private GuardResolution $guard
    ) {}

    /**
     * Enforce security policies for the requested service.
     *
     * @param KernelContext $context The resolution context.
     * @return void
     * @throws ContainerException If policy validation fails.
     * @see docs/Core/Kernel/Steps/GuardPolicyStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context): void
    {
        if ($context->getMeta('inject', 'target', false)) {
            return;
        }

        $result = $this->guard->check(abstract: $context->serviceId);

        if ($result instanceof ErrorDTO) {
            throw new ContainerException(
                message: sprintf('Policy violation for service "%s": %s', $context->serviceId, $result->message),
                code: (int) $result->code
            );
        }

        // Add policy validation metadata
        $context->setMeta('policy', 'checked', true);
        $context->setMeta('policy', 'check_time', microtime(as_float: true));
    }
}
