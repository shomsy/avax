<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\PublicSurface;

use Avax\Labs\API\DescribeApi\System\Capabilities\BreakingChanges\BreakingChangeDetector;
use Avax\Labs\API\DescribeApi\System\Capabilities\BreakingChanges\BreakingChangeReport;
use Avax\Labs\API\DescribeApi\System\Capabilities\EndpointContracts\EndpointContract;
use Avax\Labs\API\DescribeApi\System\Capabilities\ReadApiDescriptions\ReadApiDescriptions;
use Avax\Labs\API\DescribeApi\System\Capabilities\ReadApiDescriptions\ReadFakeApiDescriptions;
use Avax\Labs\API\DescribeApi\System\Flows\DescribeHttpContracts\DescribeHttpContracts;
use Avax\Labs\API\DescribeApi\System\Flows\DetectBreakingApiChanges\DetectBreakingApiChanges;
use Avax\Labs\API\DescribeApi\System\Flows\GenerateApiContractTests\GenerateApiContractTests;
use Avax\Labs\API\DescribeApi\System\Flows\ValidateApiContracts\ValidateApiContracts;

final class ApiContracts
{
    public function __construct(
        private readonly ReadApiDescriptions $apiContracts,
        private readonly DescribeHttpContracts $describeHttpContracts,
        private readonly ValidateApiContracts $validateApiContracts,
        private readonly DetectBreakingApiChanges $detectBreakingApiChanges,
        private readonly GenerateApiContractTests $generateApiContractTests,
    ) {
    }

    public static function inMemory(): self
    {
        $adapter = new ReadFakeApiDescriptions();

        return new self(
            apiContracts: $adapter,
            describeHttpContracts: new DescribeHttpContracts(apiContracts: $adapter),
            validateApiContracts: new ValidateApiContracts(),
            detectBreakingApiChanges: new DetectBreakingApiChanges(
                breakingChangeDetector: new BreakingChangeDetector(),
            ),
            generateApiContractTests: new GenerateApiContractTests(),
        );
    }

    public function registerEndpoint(EndpointContract $endpoint): self
    {
        $this->apiContracts->registerEndpoint(endpoint: $endpoint);

        return $this;
    }

    public function validate(): ApiContractReport
    {
        return $this->validateApiContracts->validate(apiContract: $this->describe());
    }

    public function describe(): ApiContract
    {
        return $this->describeHttpContracts->describe();
    }

    public function detectBreakingChanges(ApiContract $oldContract): BreakingChangeReport
    {
        return $this->detectBreakingApiChanges->detect(
            oldContract: $oldContract,
            newContract: $this->describe(),
        );
    }

    /**
     * @return list<string>
     */
    public function contractTestScenarios(): array
    {
        return $this->generateApiContractTests->scenariosFor(apiContract: $this->describe());
    }
}
