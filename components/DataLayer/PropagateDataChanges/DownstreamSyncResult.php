<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

final readonly class DownstreamSyncResult
{
    public function __construct(
        public string $targetSystem,
        public bool   $success,
        public array  $syncedIds,
        public array  $errors,
        public int    $durationMs
    ) {}

    public static function success(string $targetSystem, array $syncedIds, int $durationMs) : self
    {
        return new self(
            targetSystem: $targetSystem,
            success     : true,
            syncedIds   : $syncedIds,
            errors      : [],
            durationMs  : $durationMs
        );
    }

    public static function failure(string $targetSystem, array $errors, int $durationMs) : self
    {
        return new self(
            targetSystem: $targetSystem,
            success     : false,
            syncedIds   : [],
            errors      : $errors,
            durationMs  : $durationMs
        );
    }

    public function describeResponsibility() : string
    {
        return 'records result of syncing changes to downstream system.';
    }

    public function toMetadata() : array
    {
        return [
            'target_system' => $this->targetSystem,
            'success'       => $this->success,
            'synced_ids'    => $this->syncedIds,
            'errors'        => $this->errors,
            'duration_ms'   => $this->durationMs,
        ];
    }
}