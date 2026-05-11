<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting;

use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\Verification\BreakingChangeDetector;
use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\Verification\ContractVerifier;

final readonly class ContractTesting
{
    public static function verify() : ContractVerificationReport
    {
        $contractVerifier = new ContractVerifier();

        return $contractVerifier->verify();
    }

    public static function verifyComponent(string $componentClass) : ComponentContractResult
    {
        $contractVerifier = new ContractVerifier();

        return $contractVerifier->verifyComponent($componentClass);
    }

    public static function breakingChanges(string $sinceVersion) : BreakingChangesReport
    {
        $breakingChangeDetector = new BreakingChangeDetector();

        return $breakingChangeDetector->detect($sinceVersion);
    }

    public static function registerContract(string $componentClass, array $contract) : void
    {
        $contractVerifier = new ContractVerifier();
        $contractVerifier->registerContract($componentClass, $contract);
    }
}

final readonly class ContractVerificationReport
{
    public function __construct(
        /** @var list<ComponentContractResult> */
        public array $results = []
    ) {}

    public function toArray() : array
    {
        return [
            'passed'  => $this->passed(),
            'results' => array_map(
                static fn (ComponentContractResult $componentContractResult) : array => $componentContractResult->toArray(),
                $this->results,
            ),
        ];
    }

    public function passed() : bool
    {
        return array_all($this->results, fn ($result) => $result->passed);
    }
}

final readonly class ComponentContractResult
{
    public function __construct(
        public string  $component,
        public bool    $passed,
        public array   $checks = [],
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
                static fn ($c) : array => is_array($c) ? $c : (array) $c,
                $this->changes,
            ),
        ];
    }

    public function hasBreaking() : bool
    {
        return $this->changes !== [];
    }
}
