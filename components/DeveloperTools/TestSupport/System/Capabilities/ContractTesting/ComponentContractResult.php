<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\TestSupport\System\Capabilities\ContractTesting;

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
