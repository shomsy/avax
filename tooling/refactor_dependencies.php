<?php

declare(strict_types=1);

$replacements = [
    // 1. Base Container classes
    'ServiceProviderInterface' => 'RegisterDependency',
    'ServiceProvider'          => 'BaseRegisterDependency', // Important: This must run after ServiceProviderInterface
    'DeferredProviderInterface' => 'RegisterDeferredDependency',

    // 2. Container Actions & Artifacts
    'ServiceResolver'          => 'ResolveDependency',
    'ServicePool'              => 'DependencyPool',
    'ServiceBlueprint'         => 'DependencyBlueprint',
    'CreateServiceBlueprint'   => 'CreateDependencyBlueprint',
    'ServiceRegistryInterface' => 'DependencyRegistryContract',
    'ServiceRegistry'          => 'DependencyRegistry',
    'ServiceRegistration'      => 'DependencyRegistration',
    'SeedSystemServices'       => 'SeedSystemDependencies',
    'ServiceCompiler'          => 'DependencyCompiler',
    'ServiceNotFoundException' => 'DependencyNotFoundException',
    'RegisterServices'         => 'RegisterDependencies',

    // 3. Framework specific
    'ContainerServiceExplanation' => 'ContainerDependencyExplanation',

    // 4. Specific Providers -> Registrars -> Actions
    // We already renamed some to *Registrar in the last session, so we need to catch both
    'AuthServiceProvider'      => 'RegisterAuthDependencies',
    'AuthRegistrar'            => 'RegisterAuthDependencies',

    'DatabaseServiceProvider' => 'RegisterDatabaseDependencies',
    'DatabaseRegistrar'        => 'RegisterDatabaseDependencies',

    'CacheServiceProvider' => 'RegisterCacheDependencies',
    'CacheRegistrar'           => 'RegisterCacheDependencies',

    'RegisterSecurityServices' => 'RegisterSecurityDependencies',
    'RegisterTokenServices'    => 'RegisterTokenDependencies',
    'RegisterAccessServices'   => 'RegisterAccessDependencies',
    'RegisterViewServices'     => 'RegisterViewDependencies',
    'RegisterDataServices'     => 'RegisterDataDependencies',
    'RegisterMailServices'     => 'RegisterMailDependencies',
    'RegisterQueueServices'    => 'RegisterQueueDependencies',
    'RegisterNotificationServices' => 'RegisterNotificationDependencies',
    'RegisterEventServices'    => 'RegisterEventDependencies',
    'RegisterDateTimeServices' => 'RegisterDateTimeDependencies',

    // 5. Shared/Unsupported
    'SharedLifetime'           => 'SingletonLifetime',
    'UnsupportedDiskDriver'    => 'InvalidDiskDriver',
    'UnsupportedTarget'        => 'InvalidTarget',

    // 6. Helpers
    "'helpers.php'"            => "'shortcuts.php'",
    '"helpers.php"'            => '"shortcuts.php"',
];

// Re-sort replacements to avoid partial overlaps (longest strings first)
uksort($replacements, static fn ($a, $b) => strlen($b) <=> strlen($a));

$directories = ['components', 'framework', 'tests'];

foreach ($directories as $dir) {
    if (! is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $path    = $file->getPathname();
            $content = file_get_contents($path);
            $original = $content;

            // Execute replacements
            foreach ($replacements as $old => $new) {
                $content = str_replace($old, $new, $content);
            }

            // Save if changed
            if ($content !== $original) {
                file_put_contents($path, $content);
                echo "Updated: $path\n";
            }
        }
    }
}

echo "✨ All internal class names, use statements, and namespaces updated successfully!\n";
