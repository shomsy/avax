<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety;

/**
 * Runtime safety manager for long-lived runtimes.
 *
 * Coordinates state leak detection, static state scanning,
 * reset verification, and request scope enforcement.
 */
final class RuntimeSafety
{
    private StateLeakDetection\StateLeakDetector $leakDetector;
    private StaticStateScanner                   $staticScanner;
    private ResetVerification\ResetVerifier      $resetVerifier;

    public function __construct(
        StateLeakDetection\StateLeakDetector|null $leakDetector = null,
        StaticStateScanner|null                   $staticScanner = null,
        ResetVerification\ResetVerifier|null      $resetVerifier = null,
    )
    {
        $this->leakDetector  = $leakDetector ?? new StateLeakDetection\StateLeakDetector();
        $this->staticScanner = $staticScanner ?? new StaticStateScanner();
        $this->resetVerifier = $resetVerifier ?? new ResetVerification\ResetVerifier();
    }

    /**
     * Check if runtime is safe for worker mode.
     */
    public function isWorkerSafe() : bool
    {
        $findings = $this->inspect();

        foreach ($findings as $finding) {
            if ($finding->severity === RuntimeSafetyFinding::SEVERITY_CRITICAL) {
                return false;
            }
        }

        return true;
    }

    /**
     * Run full runtime safety inspection.
     *
     * @return list<RuntimeSafetyFinding>
     */
    public function inspect() : array
    {
        $findings = [];

        $findings = [...$findings, ...$this->leakDetector->detect()];
        $findings = [...$findings, ...$this->staticScanner->scan()];
        $findings = [...$findings, ...$this->resetVerifier->verify()];

        return $findings;
    }

    public function leakDetector() : StateLeakDetection\StateLeakDetector
    {
        return $this->leakDetector;
    }

    public function staticScanner() : StaticStateScanner
    {
        return $this->staticScanner;
    }

    public function resetVerifier() : ResetVerification\ResetVerifier
    {
        return $this->resetVerifier;
    }
}
