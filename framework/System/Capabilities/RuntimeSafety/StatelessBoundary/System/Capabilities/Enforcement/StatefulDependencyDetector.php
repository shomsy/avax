<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety\StatelessBoundary\System\Capabilities\Enforcement;

use Closure;

final class StatefulDependencyDetector
{
    /** @var array<string, Closure> */
    private array $detectors = [];

    public function register(string $dependency, Closure $detector) : void
    {
        $this->detectors[$dependency] = $detector;
    }

    public function isStateful(string $dependency) : bool
    {
        $detector = $this->detectors[$dependency] ?? null;

        return $detector ? $detector() : false;
    }
}
