<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\System\PublicSurface;

use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\System\Capabilities\Verification\BreakingChangeDetector;
use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\System\Capabilities\Verification\ContractVerifier;

final readonly class ContractTesting
{
    public static function verify() : ContractVerificationReport
    {
        $verifier = new ContractVerifier();

        return $verifier->verify();
    }

    public static function verifyComponent(string $componentClass) : ComponentContractResult
    {
        $verifier = new ContractVerifier();

        return $verifier->verifyComponent($componentClass);
    }

    public static function breakingChanges(string $sinceVersion) : BreakingChangesReport
    {
        $detector = new BreakingChangeDetector();

        return $detector->detect($sinceVersion);
    }

    public static function registerContract(string $componentClass, array $contract) : void
    {
        $verifier = new ContractVerifier();
        $verifier->registerContract($componentClass, $contract);
    }
}

final readonly class ContractVerificationReport
{
    /** @var list<ComponentContractResult> */
    public array $results;

    public function __construct(array $results = [])
    {
        $this->results = $results;
    }

    public function toArray() : array
    {
        return [
            'passed'  => $this->passed(),
            'results' => array_map(
                fn (ComponentContractResult $r) => $r->toArray(),
                $this->results,
            ),
        ];
    }

    public function passed() : bool
    {
        foreach ($this->results as $result) {
            if (! $result->passed) {
                return false;
            }
        }

        return true;
    }
}

final readonly class ComponentContractResult
{
    public function __construct(
        public string      $component,
        public bool        $passed,
        public array       $checks = [],
        public string|null $error = null,
    ) {}

    public function toArray() : array
    {
        return [
            'component' => $this->component,
            'passed'    => $this->passed,
            'checks'    => $this->checks,
            'error'     => $this->error,
        ];
    }
}

final readonly class BreakingChangesReport
{
    public function __construct(
        public array $changes = [],
    ) {}

    public function toArray() : array
    {
        return [
            'has_breaking' => $this->hasBreaking(),
            'changes'      => array_map(
                fn ($c) => is_array($c) ? $c : (array) $c,
                $this->changes,
            ),
        ];
    }

    public function hasBreaking() : bool
    {
        return count($this->changes) > 0;
    }
}