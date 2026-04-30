<?php

$dirs    = ['framework', 'components', 'tests'];
$baseDir = realpath(__DIR__);

$mappings = [
    'Avax\HTTP'                 => 'Avax\Components\HTTP',
    'Avax\Database'             => 'Avax\Components\DataStack\Database',
    'Avax\Cache'                => 'Avax\Components\Application\Cache',
    'Avax\Config'               => 'Avax\Components\Application\Config',
    'Avax\Container'            => 'Avax\Components\Application\Container',
    'Avax\FeatureFlags'         => 'Avax\Components\FeatureFlags',
    'Avax\Resilience'           => 'Avax\Components\Resilience',
    'Avax\Concurrency'          => 'Avax\Components\Concurrency',
    'Avax\Tasks'                => 'Avax\Components\Tasks',
    'Avax\Security'             => 'Avax\Components\Security',
    'Avax\HealthCheck'          => 'Avax\Components\HealthCheck',
    'Avax\QueryGovernance'      => 'Avax\Components\QueryGovernance',
    'Avax\Scheduler'            => 'Avax\Components\Scheduler',
    'Avax\Realtime'             => 'Avax\Components\Realtime',
    'Avax\Secrets'              => 'Avax\Components\Secrets',
    'Avax\ApiVersioning'        => 'Avax\Components\ApiVersioning',
    'Avax\Policy'               => 'Avax\Components\Policy',
    'Avax\Tenancy'              => 'Avax\Components\Tenancy',
    'Avax\ServiceMap'           => 'Avax\Components\ServiceMap',
    'Avax\Fallback'             => 'Avax\Components\Fallback',
    'Avax\MessageBus'           => 'Avax\Components\MessageBus',
    'Avax\JwtAuth'              => 'Avax\Components\JwtAuth',
    'Avax\EnvironmentAwareness' => 'Avax\Components\EnvironmentAwareness',
    'Avax\WorkerManager'        => 'Avax\Components\WorkerManager',
    'Avax\ExternalState'        => 'Avax\Components\ExternalState',
    'Avax\Orchestration'        => 'Avax\Components\Orchestration',
    'Avax\AfterResponse'        => 'Avax\Components\AfterResponse',
    'Avax\StatelessBoundary'    => 'Avax\Components\StatelessBoundary',
    'Avax\TaskDispatch'         => 'Avax\Components\TaskDispatch',
    'Avax\ScalingReadiness'     => 'Avax\Components\ScalingReadiness',
    'Avax\GracefulShutdown'     => 'Avax\Components\GracefulShutdown',
    'Avax\ResourceGovernor'     => 'Avax\Components\ResourceGovernor',
    'Avax\Idempotency'          => 'Avax\Components\Idempotency',
    'Avax\ContentNegotiation'   => 'Avax\Components\ContentNegotiation',
    'Avax\ContractTesting'      => 'Avax\Components\ContractTesting',
    'Avax\Pipeline'             => 'Avax\Components\Pipeline',
];

foreach ($dirs as $dir) {
    $targetDir = $baseDir . '/' . $dir;
    if (! is_dir($targetDir)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($targetDir));

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;

        $path     = $file->getPathname();
        $content  = file_get_contents($path);
        $original = $content;

        foreach ($mappings as $old => $new) {
            // Zameni samo ako nije već zamenjeno (da izbegnemo Avax\Components\Components)
            // Koristimo negativni lookbehind u str_replace logici
            if (str_contains($content, $old) && ! str_contains($content, 'Components\\' . str_replace('Avax\\', '', $old))) {
                $content = str_replace($old, $new, $content);
            }
        }

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "✨ Purified: " . str_replace($baseDir . '/', '', $path) . "\n";
        }
    }
}

echo "\n🚀 Final Purification Complete. Legacy namespaces annihilated.\n";
