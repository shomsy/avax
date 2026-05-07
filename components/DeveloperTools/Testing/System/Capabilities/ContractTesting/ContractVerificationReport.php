<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting;

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
