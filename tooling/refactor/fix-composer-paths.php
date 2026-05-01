<?php

declare(strict_types=1);

/**
 * Fix composer.json autoload files - remove missing, map known moves.
 */

$json = json_decode(file_get_contents('composer.json'), true);

$oldToNew = [
    'components/FeatureFlags/System/PublicSurface/FeatureFlags.php'             => 'components/Application/FeatureFlags/System/PublicSurface/FeatureFlags.php',
    'components/Pipeline/System/PublicSurface/Pipeline.php'                     => 'components/Application/Pipeline/System/PublicSurface/Pipeline.php',
    'components/ApiVersioning/System/PublicSurface/ApiVersion.php'              => 'components/HTTP/ApiVersioning/System/PublicSurface/ApiVersion.php',
    'components/AfterResponse/System/PublicSurface/AfterResponse.php'           => 'components/HTTP/AfterResponse/System/PublicSurface/AfterResponse.php',
    'components/ContentNegotiation/System/PublicSurface/ContentNegotiation.php' => 'components/HTTP/ContentNegotiation/System/PublicSurface/ContentNegotiation.php',
    'components/Resilience/System/PublicSurface/Resilience.php'                 => 'components/Operations/Resilience/System/PublicSurface/Resilience.php',
    'components/Concurrency/System/PublicSurface/Concurrency.php'               => 'components/Operations/Concurrency/System/PublicSurface/Concurrency.php',
    'components/Realtime/System/PublicSurface/Realtime.php'                     => 'components/Operations/Realtime/System/PublicSurface/Realtime.php',
    'components/MessageBus/System/PublicSurface/MessageBus.php'                 => 'components/Operations/MessageBus/System/PublicSurface/MessageBus.php',
    'components/Security/System/PublicSurface/Security.php'                     => 'components/Security/System/PublicSurface/Security.php',
    'components/Policy/System/PublicSurface/Policy.php'                         => 'components/Identity/Access/System/Capabilities/Policy/System/PublicSurface/Policy.php',
    'components/Tenancy/System/PublicSurface/Tenancy.php'                       => 'components/Identity/Tenancy/System/PublicSurface/Tenancy.php',
    'components/JwtAuth/System/PublicSurface/JwtAuth.php'                       => 'components/Identity/Tokens/System/Capabilities/JwtAuth/System/PublicSurface/JwtAuth.php',
    'components/ScalingReadiness/System/PublicSurface/ScalingReadiness.php'     => 'components/DeveloperTools/Diagnostics/System/Capabilities/ScalingReadiness/System/PublicSurface/ScalingReadiness.php',
    'components/ContractTesting/System/PublicSurface/ContractTesting.php'       => 'components/DeveloperTools/Testing/System/Capabilities/ContractTesting/System/PublicSurface/ContractTesting.php',
    'components/QueryGovernance/System/PublicSurface/QueryGovernance.php'       => 'components/DataStack/Database/System/Capabilities/QueryGovernance/System/PublicSurface/QueryGovernance.php',
    'components/EnvironmentAwareness/System/PublicSurface/Environment.php'      => 'components/Application/Config/System/Capabilities/EnvironmentAwareness/System/PublicSurface/Environment.php',
    'components/Fallback/System/PublicSurface/Fallback.php'                     => 'components/Operations/Resilience/System/Capabilities/Fallback/System/PublicSurface/Fallback.php',
    'components/Idempotency/System/PublicSurface/Idempotency.php'               => 'components/Operations/Resilience/System/Capabilities/Idempotency/System/PublicSurface/Idempotency.php',
];

$files    = $json['autoload']['files'] ?? [];
$newFiles = [];

foreach ($files as $file) {
    if (! file_exists($file)) {
        echo "REMOVED (missing): $file\n";

        continue;
    }

    if (isset($oldToNew[$file])) {
        $newPath = $oldToNew[$file];
        if (file_exists($newPath)) {
            $newFiles[] = $newPath;
            echo "MAPPED: {$file} -> {$newPath}\n";
        } else {
            $newFiles[] = $file;
            echo "KEPT (new not exists): {$file}\n";
        }
    } else {
        $newFiles[] = $file;
    }
}

$json['autoload']['files'] = $newFiles;

if (isset($json['suggest']) && $json['suggest'] === []) {
    unset($json['suggest']);
}

file_put_contents('composer.json', json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
