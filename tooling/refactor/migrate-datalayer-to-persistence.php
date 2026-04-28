<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * DataLayer to Persistence Migration Script
 * 
 * Replaces:
 * - Avax\DataLayer\* -> Avax\Components\Persistence\System\*
 */
final class MigrateDataLayerToPersistence
{
    private const MIGRATIONS = [
        'Avax\DataLayer\AccessPersistentData\AccessPersistentData' => 'Avax\Components\Persistence\System\Capabilities\Repositories\Repository',
        'Avax\DataLayer\AccessPersistentData\PersistentDataFailure' => 'Avax\Components\Persistence\System\Foundation\Failure\PersistenceFailure',
        'Avax\DataLayer\AccessPersistentData\PersistentDataRequest' => 'Avax\Components\Persistence\System\Capabilities\Repositories\Repository',
        'Avax\DataLayer\AccessPersistentData\PersistentDataResult' => 'Avax\Components\Persistence\System\Capabilities\Repositories\Repository',
        
        'Avax\DataLayer\CommitDataChanges\CommitDataChanges' => 'Avax\Components\Persistence\System\Capabilities\UnitOfWork\UnitOfWork',
        'Avax\DataLayer\CommitDataChanges\DataTransactionFailure' => 'Avax\Components\Persistence\System\Foundation\Failure\PersistenceFailure',
        'Avax\DataLayer\CommitDataChanges\DataTransactionPolicy' => 'Avax\Components\Persistence\System\Capabilities\UnitOfWork\UnitOfWorkInterface',
        
        'Avax\DataLayer\ConfigureDataLayer\DataLayerConfig' => 'Avax\Components\Persistence\System\Configuration\PersistenceBuilder',
        'Avax\DataLayer\ConfigureDataLayer\DataLayerConfigurationFailure' => 'Avax\Components\Persistence\System\Foundation\Failure\PersistenceFailure',
        'Avax\DataLayer\ConfigureDataLayer\RegisterDataLayerRuntime' => 'Avax\Components\Persistence\System\Configuration\PersistenceBuilder',
        'Avax\DataLayer\ConfigureDataLayer\ResolveDataLayerRuntime' => 'Avax\Components\Persistence\System\Configuration\PersistenceBuilder',
        
        'Avax\DataLayer\DataLayer' => 'Avax\Components\Persistence\System\PublicSurface\Persistence',
        
        // Saga references
        'Avax\DataLayer\ProtectStoredData\TenantBoundary' => 'Avax\Components\Persistence\System\Capabilities\Repositories\Repository',
    ];

    public function migrate(string $rootPath = 'components'): array
    {
        $changed = [];
        $errors = [];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;
            if ($file->getExtension() !== 'php') continue;
            
            $content = file_get_contents($file->getPathname());
            if ($content === false) continue;
            
            $original = $content;
            
            foreach (self::MIGRATIONS as $old => $new) {
                $content = str_replace(
                    'use ' . $old . ';',
                    'use ' . $new . ';',
                    $content
                );
            }
            
            if ($content !== $original) {
                $filePath = $file->getPathname();
                if (file_put_contents($filePath, $content) !== false) {
                    $changed[] = $filePath;
                } else {
                    $errors[] = $filePath . ' (write failed)';
                }
            }
        }

        return [
            'changed' => count($changed),
            'files' => $changed,
            'errors' => count($errors),
            'error_details' => $errors,
        ];
    }
}

if (php_sapi_name() === 'cli') {
    $migrator = new MigrateDataLayerToPersistence();
    $results = $migrator->migrate('components');
    
    echo "DataLayer -> Persistence Migration\n";
    echo "======================================\n\n";
    echo "Files changed: {$results['changed']}\n";
    echo "Errors: {$results['errors']}\n\n";
    
    if ($results['changed'] > 0) {
        echo "Modified files:\n";
        foreach ($results['files'] as $file) {
            echo "  - $file\n";
        }
    }
    
    if ($results['errors'] > 0) {
        echo "\nErrors:\n";
        foreach ($results['error_details'] as $error) {
            echo "  - $error\n";
        }
    }
    
    exit($results['errors'] > 0 ? 1 : 0);
}