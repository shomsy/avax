<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps;

use Avax\Container\DependencyInjection\Capabilities\Policies\CheckResolutionPolicy;
use Avax\Container\DependencyInjection\Capabilities\Policies\Decisions\ResolutionBlocked;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Errors\ContainerException;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel\KernelContext;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Contracts\KernelStep;

/**
 * Pipeline step that blocks disallowed resolutions before construction starts.
 */
final readonly class EnforcePolicyStep implements KernelStep
{
    public function __construct(
        private CheckResolutionPolicy $check
    ) {}

    public function __invoke(KernelContext $context) : void
    {
        if ($context->getMeta(namespace: 'inject', key: 'target', default: false)) {
            return;
        }

        $result = $this->check->check(abstract: $context->serviceId);

        if ($result instanceof ResolutionBlocked) {
            throw new ContainerException(
                message: sprintf('Policy violation for service "%s": %s', $context->serviceId, $result->message)
            );
        }

        $context->setMeta(namespace: 'policy', key: 'checked', value: true);
        $context->setMeta(namespace: 'policy', key: 'check_time', value: microtime(as_float: true));
    }
}
