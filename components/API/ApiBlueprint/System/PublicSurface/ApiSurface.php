<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\PublicSurface;

use Avax\Components\API\Surface\System\Capabilities\Compatibility\CompatibilityChangeDetector;
use Avax\Components\API\Surface\System\Capabilities\Compatibility\CompatibilityReport;
use Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions\EndpointDefinition;
use Avax\Components\API\Surface\System\Capabilities\GenerateApiDocumentation\ApiDocumentationSource;
use Avax\Components\API\Surface\System\Capabilities\GenerateApiDocumentation\InMemoryApiDocumentationSource;
use Avax\Components\API\Surface\System\Flows\BuildApiSurface\BuildApiSurface;
use Avax\Components\API\Surface\System\Flows\DetectApiCompatibilityChanges\DetectApiCompatibilityChanges;
use Avax\Components\API\Surface\System\Flows\GenerateApiCompatibilityChecks\GenerateApiCompatibilityChecks;
use Avax\Components\API\Surface\System\Flows\ValidateApiSurface\ValidateApiSurface;

final class ApiSurface
{
    public function __construct(
        private readonly ApiDocumentationSource         $apiDocumentation,
        private readonly BuildApiSurface                $buildApiSurface,
        private readonly ValidateApiSurface             $validateApiSurface,
        private readonly DetectApiCompatibilityChanges  $detectApiCompatibilityChanges,
        private readonly GenerateApiCompatibilityChecks $generateApiCompatibilityChecks,
    ) {}

    public static function inMemory() : self
    {
        $adapter = new InMemoryApiDocumentationSource();

        return new self(
            apiDocumentation              : $adapter,
            buildApiSurface               : new BuildApiSurface(apiDocumentation: $adapter),
            validateApiSurface            : new ValidateApiSurface(),
            detectApiCompatibilityChanges : new DetectApiCompatibilityChanges(
                                                compatibilityChangeDetector: new CompatibilityChangeDetector(),
                                            ),
            generateApiCompatibilityChecks: new GenerateApiCompatibilityChecks(),
        );
    }

    public function registerEndpoint(EndpointDefinition $endpoint) : self
    {
        $this->apiDocumentation->registerEndpoint(endpoint: $endpoint);

        return $this;
    }

    public function validate() : ApiSurfaceReport
    {
        return $this->validateApiSurface->validate(surface: $this->describe());
    }

    public function describe() : ApiSurfaceDefinition
    {
        return $this->buildApiSurface->build();
    }

    public function detectCompatibility(ApiSurfaceDefinition $oldSurface) : CompatibilityReport
    {
        return $this->detectApiCompatibilityChanges->detect(
            oldSurface: $oldSurface,
            newSurface: $this->describe(),
        );
    }

    /**
     * @return list<string>
     */
    public function compatibilityCheckScenarios() : array
    {
        return $this->generateApiCompatibilityChecks->scenariosFor(surface: $this->describe());
    }
}
