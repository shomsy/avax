<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting;

use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\Verification\BreakingChangeDetector;
use Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\Verification\ContractVerifier;

final readonly class ComponentContractResult
{
    public function __construct(
        public string  $component,
        public bool    $passed,
        public array   $checks = [],
        public ?string $error = null,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'component' => $this->component,
            'passed' => $this->passed,
            'checks' => $this->checks,
            'error' => $this->error,
        ];
    }
}
