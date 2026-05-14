<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunFallbackAction;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePipelineResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use Throwable;

/**
 * RunFallbackAction — Executes a fallback handler when the primary action fails.
 */
final readonly class RunFallbackAction
{
    /**
     * @throws \RuntimeException When no fallback class is configured, class not found, or class lacks __invoke
     */
    public function execute(
        Throwable $failure,
        FailureContext $context,
        FailurePolicy $policy,
    ): mixed {
        $fallbackClass = $policy->fallbackClass;

        if ($fallbackClass === null) {
            throw new \RuntimeException('No fallback class configured');
        }

        if (!class_exists($fallbackClass)) {
            throw new \RuntimeException("Fallback class not found: {$fallbackClass}");
        }

        $handler = new $fallbackClass();

        if (!method_exists($handler, '__invoke')) {
            throw new \RuntimeException("Fallback class must implement __invoke: {$fallbackClass}");
        }

        $result = $handler($failure, $context);
        return FailurePipelineResult::fallback($result);
    }
}
