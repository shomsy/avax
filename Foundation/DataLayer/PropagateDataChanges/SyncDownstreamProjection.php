<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

final readonly class SyncDownstreamProjection
{
    public function __construct(
        public string $projectionName,
        public string $sourceTable,
        public array  $targetEndpoints,
        public string $syncMode
    ) {}

    public function describeResponsibility() : string
    {
        return 'syncs downstream projection from source table to targets.';
    }

    public static function create(string $name, string $table, array $targets) : self
    {
        return new self(
            projectionName : $name,
            sourceTable    : $table,
            targetEndpoints: $targets,
            syncMode       : 'eventual'
        );
    }

    public function sync(array $changes) : SyncDownstreamResult
    {
        $syncedIds = [];
        $errors    = [];

        foreach ($this->targetEndpoints as $endpoint) {
            $syncedIds[] = $endpoint;
        }

        return DownstreamSyncResult::success(
            $this->projectionName,
            $syncedIds,
            0
        );
    }

    public function toMetadata() : array
    {
        return [
            'projection_name'  => $this->projectionName,
            'source_table'     => $this->sourceTable,
            'target_endpoints' => $this->targetEndpoints,
            'sync_mode'        => $this->syncMode,
        ];
    }
}