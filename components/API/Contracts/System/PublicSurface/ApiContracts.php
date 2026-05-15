<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\PublicSurface;

use Avax\Components\API\Contracts\System\Capabilities\BreakingChangeDetector\BreakingChangeDetector;
use Avax\Components\API\Contracts\System\Capabilities\CompatibilityChecker\CompatibilityChecker;
use Avax\Components\API\Contracts\System\Capabilities\DeprecationTracker\DeprecationTracker;
use Avax\Components\API\Contracts\System\Capabilities\EndpointRegistry\EndpointRegistry;
use Avax\Components\API\Contracts\System\Capabilities\Versioning\ApiVersion;
use Avax\Components\API\Contracts\System\Configuration\ApiContractsConfiguration;
use Avax\Components\API\Contracts\System\Flows\DeprecateEndpoint\DeprecateEndpoint;
use Avax\Components\API\Contracts\System\Flows\DetectBreakingChange\DetectBreakingChange;
use Avax\Components\API\Contracts\System\Flows\RegisterApiVersion\RegisterApiVersion;
use Avax\Components\API\Contracts\System\Flows\ValidateApiContract\ValidateApiContract;

final class ApiContracts
{
    public static function registry() : EndpointRegistry
    {
        return new EndpointRegistry();
    }

    public static function deprecationTracker() : DeprecationTracker
    {
        return new DeprecationTracker();
    }

    /**
     * @param array<string, mixed> $oldSchema
     * @param array<string, mixed> $newSchema
     *
     * @return array{has_breaking_changes:bool,breaking_changes:list<array{type:string,path:string,message:string,severity:string}>,total_changes:int}
     */
    public static function detectBreakingChanges(array $oldSchema, array $newSchema) : array
    {
        return (new DetectBreakingChange())->execute(oldSchema: $oldSchema, newSchema: $newSchema);
    }

    /**
     * @param array<string, mixed> $oldSchema
     * @param array<string, mixed> $newSchema
     *
     * @return array{compatible:bool,changes:list<array{type:string,path:string,message:string,severity:string}>}
     */
    public static function checkCompatibility(array $oldSchema, array $newSchema) : array
    {
        return (new CompatibilityChecker())->check(oldSchema: $oldSchema, newSchema: $newSchema);
    }

    /**
     * @return array{valid:bool,errors:list<string>,count:int}
     */
    public static function validateEndpoints(EndpointRegistry $registry) : array
    {
        return (new ValidateApiContract())->execute(registry: $registry);
    }

    /**
     * @return array{version:string,major:string,minor:string,patch:string,current:string,is_major_change:bool,is_compatible:bool}
     */
    public static function registerVersion(string $version, ApiContractsConfiguration $config) : array
    {
        return (new RegisterApiVersion(config: $config))->execute(version: $version);
    }

    /**
     * @return array{deprecated:string,sunset_date:string,replacement:string,reason:string}
     */
    public static function deprecate(
        DeprecationTracker $tracker,
        string             $method,
        string             $path,
        string             $sunsetDate,
        string             $replacement = '',
        string             $reason = '',
    ) : array
    {
        return (new DeprecateEndpoint())->execute(
            tracker    : $tracker,
            method     : $method,
            path       : $path,
            sunsetDate : $sunsetDate,
            replacement: $replacement,
            reason     : $reason,
        );
    }

    public static function parseVersion(string $version) : ApiVersion
    {
        return ApiVersion::fromString(version: $version);
    }
}
