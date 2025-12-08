<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use Avax\HTTP\Session\Contracts\Storage\Store;

class SessionMigrator
{
    private Store $sourceStore;
    private Store $destinationStore;
    private int $batchSize;
    private array $migrationStats = [
        'total' => 0,
        'migrated' => 0,
        'failed' => 0,
        'errors' => []
    ];

    public function __construct(
        Store $sourceStore,
        Store $destinationStore,
        int $batchSize = 1000
    ) {
        $this->sourceStore = $sourceStore;
        $this->destinationStore = $destinationStore;
        $this->batchSize = max(1, $batchSize);
    }

    public function migrate(callable $progressCallback = null): array
    {
        $this->resetStats();
        
        try {
            // Get all session data from source
            $sessionData = $this->sourceStore->all();
            $this->migrationStats['total'] = count($sessionData);
            
            $batch = [];
            $batchCount = 0;
            
            foreach ($sessionData as $sessionId => $data) {
                try {
                    $batch[$sessionId] = $data;
                    $batchCount++;
                    
                    if ($batchCount >= $this->batchSize) {
                        $this->migrateBatch($batch);
                        $batch = [];
                        $batchCount = 0;
                        
                        if (is_callable($progressCallback)) {
                            $progressCallback($this->migrationStats);
                        }
                    }
                } catch (\Throwable $e) {
                    $this->handleMigrationError($sessionId, $e);
                }
            }
            
            // Migrate any remaining items in the last batch
            if (!empty($batch)) {
                $this->migrateBatch($batch);
            }
            
            return $this->migrationStats;
            
        } catch (\Throwable $e) {
            $this->migrationStats['errors'][] = [
                'type' => 'global',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
            return $this->migrationStats;
        }
    }

    private function migrateBatch(array $batch): void
    {
        foreach ($batch as $sessionId => $data) {
            try {
                $this->destinationStore->put($sessionId, $data);
                $this->migrationStats['migrated']++;
            } catch (\Throwable $e) {
                $this->handleMigrationError($sessionId, $e);
            }
        }
    }

    private function handleMigrationError(string $sessionId, \Throwable $e): void
    {
        $this->migrationStats['failed']++;
        $this->migrationStats['errors'][] = [
            'session_id' => $sessionId,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }

    private function resetStats(): void
    {
        $this->migrationStats = [
            'total' => 0,
            'migrated' => 0,
            'failed' => 0,
            'errors' => []
        ];
    }

    public function verifyMigration(): bool
    {
        $sourceData = $this->sourceStore->all();
        $destinationData = $this->destinationStore->all();
        
        if (count($sourceData) !== count($destinationData)) {
            return false;
        }
        
        foreach ($sourceData as $key => $value) {
            if (!isset($destinationData[$key]) || $destinationData[$key] !== $value) {
                return false;
            }
        }
        
        return true;
    }
}
