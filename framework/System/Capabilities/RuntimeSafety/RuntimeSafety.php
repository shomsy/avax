<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety;

use Avax\Framework\System\Capabilities\RuntimeSafety\ResetVerification\ResetVerifier;
use Avax\Framework\System\Capabilities\RuntimeSafety\StateLeakDetection\StateLeakDetector;

/**
 * Runtime safety manager for long-lived runtimes.
 *
 * Coordinates state leak detection, static state scanning,
 * reset verification, and request scope enforcement.
 */
final readonly class RuntimeSafety
{
    public function __construct(
        private readonly StateLeakDetector  $stateLeakDetector,
        private readonly StaticStateScanner $staticStateScanner,
        private readonly ResetVerifier      $resetVerifier,
    ) {}

    /**
     * Creates a RuntimeSafety instance with default detectors.
     * Intended for diagnostic flows, CLI doctor commands, and tooling.
     * Production assembly should inject dependencies explicitly.
     */
    public static function create() : self
    {
        return new self(
            stateLeakDetector : new StateLeakDetector(),
            staticStateScanner: new StaticStateScanner(),
            resetVerifier     : new ResetVerifier(),
        );
    }

    /**
     * Check if runtime is safe for worker mode.
     */
    public function isWorkerSafe(): bool
    {
        $findings = $this->inspect();

        return array_all($findings, fn ($finding): bool => $finding->severity !== RuntimeSafetyFinding::SEVERITY_CRITICAL);
    }

    /**
     * Run full runtime safety inspection.
     *
     * @return list<RuntimeSafetyFinding>
     */
    public function inspect(): array
    {
        $findings = [];

        $findings = [...$findings, ...$this->stateLeakDetector->detect()];
        $findings = [...$findings, ...$this->staticStateScanner->scan()];

        return [...$findings, ...$this->resetVerifier->verify()];
    }

    public function leakDetector(): StateLeakDetector
    {
        return $this->stateLeakDetector;
    }

    public function staticScanner(): StaticStateScanner
    {
        return $this->staticStateScanner;
    }

    public function resetVerifier(): ResetVerifier
    {
        return $this->resetVerifier;
    }
}
