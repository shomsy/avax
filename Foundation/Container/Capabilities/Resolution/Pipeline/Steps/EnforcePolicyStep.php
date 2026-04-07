<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Resolution\Pipeline\Steps;

use Avax\Container\Capabilities\Policies\CheckResolutionPolicy;
use Avax\Container\Capabilities\Policies\Decisions\ResolutionBlocked;
use Avax\Container\Capabilities\Resolution\Kernel\KernelContext;
use Avax\Container\Capabilities\Resolution\Pipeline\Contracts\KernelStep;
use Avax\Container\Capabilities\Resolution\Errors\ContainerException;

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
