<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\PublicSurface;

use Avax\Components\API\ApiBlueprint\System\Capabilities\Compatibility\CompatibilityChangeDetector;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Compatibility\CompatibilityReport;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Documentation\ApiDocumentationSource;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Documentation\InMemoryApiDocumentationSource;
use Avax\Components\API\ApiBlueprint\System\Capabilities\EndpointDefinitions\EndpointDefinition;
use Avax\Components\API\ApiBlueprint\System\Flows\AnalyzeApiEvolution\AnalyzeApiEvolution;
use Avax\Components\API\ApiBlueprint\System\Flows\DefineApiBlueprint\DefineApiBlueprint;
use Avax\Components\API\ApiBlueprint\System\Flows\VerifyApiBlueprint\VerifyApiBlueprint;
use Avax\Components\API\ApiBlueprint\System\Flows\VerifyApiCompatibility\VerifyApiCompatibility;

final class ApiBlueprint
{
    public function __construct(
        private readonly ApiDocumentationSource $apiDocumentation,
        private readonly DefineApiBlueprint     $defineApiBlueprint,
        private readonly VerifyApiBlueprint     $verifyApiBlueprint,
        private readonly AnalyzeApiEvolution    $analyzeApiEvolution,
        private readonly VerifyApiCompatibility $verifyApiCompatibility,
    ) {}

    public static function inMemory() : self
    {
        $adapter = new InMemoryApiDocumentationSource();

        return new self(
            apiDocumentation      : $adapter,
            defineApiBlueprint    : new DefineApiBlueprint(apiDocumentation: $adapter),
            verifyApiBlueprint    : new VerifyApiBlueprint(),
            analyzeApiEvolution   : new AnalyzeApiEvolution(
                                        compatibilityChangeDetector: new CompatibilityChangeDetector(),
                                    ),
            verifyApiCompatibility: new VerifyApiCompatibility(),
        );
    }

    public function registerEndpoint(EndpointDefinition $endpoint) : self
    {
        $this->apiDocumentation->registerEndpoint(endpoint: $endpoint);

        return $this;
    }

    public function validate() : ApiBlueprintReport
    {
        return $this->verifyApiBlueprint->validate(surface: $this->describe());
    }

    public function describe() : ApiBlueprintDefinition
    {
        return $this->defineApiBlueprint->build();
    }

    public function detectCompatibility(ApiBlueprintDefinition $oldSurface) : CompatibilityReport
    {
        return $this->analyzeApiEvolution->analyze(
            oldSurface: $oldSurface,
            newSurface: $this->describe(),
        );
    }

    /**
     * @return list<string>
     */
    public function compatibilityCheckScenarios() : array
    {
        return $this->verifyApiCompatibility->scenariosFor(surface: $this->describe());
    }
}
