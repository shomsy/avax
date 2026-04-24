<?php

declare(strict_types=1);

namespace Foundation\DataLayer;

use Avax\DataLayer\AccessPersistentData\AccessPersistentData;
use Avax\DataLayer\CommitDataChanges\CommitDataChanges;
use Avax\DataLayer\ConfigureDataLayer\DataLayerConfig;
use Avax\DataLayer\ConfigureDataLayer\DataLayerRuntime;
use Avax\DataLayer\ConfigureDataLayer\ResolveDataLayerRuntime;
use Avax\DataLayer\EvolveStoredSchema\EvolveStoredSchema;
use Avax\DataLayer\InspectDataLayer\InspectDataLayer;
use Avax\DataLayer\PropagateDataChanges\PropagateDataChanges;
use Avax\DataLayer\ProtectStoredData\ProtectStoredData;
use Avax\DataLayer\QueryStoredData\QueryStoredData;
use Avax\DataLayer\ShapeStoredData\ShapeStoredData;
use Foundation\DataLayer\ConfigureDataLayer\DataLayerConfig;
use Foundation\DataLayer\ConfigureDataLayer\DataLayerRuntime;
use Foundation\DataLayer\ConfigureDataLayer\ResolveDataLayerRuntime;
use Foundation\DataLayer\AccessPersistentData\AccessPersistentData;
use Foundation\DataLayer\ShapeStoredData\ShapeStoredData;
use Foundation\DataLayer\EvolveStoredSchema\EvolveStoredSchema;
use Foundation\DataLayer\QueryStoredData\QueryStoredData;
use Foundation\DataLayer\CommitDataChanges\CommitDataChanges;
use Foundation\DataLayer\PropagateDataChanges\PropagateDataChanges;
use Foundation\DataLayer\ProtectStoredData\ProtectStoredData;
use Foundation\DataLayer\InspectDataLayer\InspectDataLayer;

final readonly class DataLayer
{
    private DataLayerRuntime $runtime;

    public function __construct(DataLayerConfig $config)
    {
        $this->runtime = ResolveDataLayerRuntime::fromConfig($config);
    }

    public function access() : AccessPersistentData
    {
        return AccessPersistentData::withRuntime($this->runtime);
    }

    public function shape() : ShapeStoredData
    {
        return new ShapeStoredData();
    }

    public function evolveSchema() : EvolveStoredSchema
    {
        return EvolveStoredSchema::withRuntime($this->runtime);
    }

    public function query() : QueryStoredData
    {
        return QueryStoredData::withRuntime($this->runtime);
    }

    public function commitChanges() : CommitDataChanges
    {
        return CommitDataChanges::withRuntime($this->runtime);
    }

    public function propagateChanges() : PropagateDataChanges
    {
        return PropagateDataChanges::withRuntime($this->runtime);
    }

    public function protectData() : ProtectStoredData
    {
        return new ProtectStoredData();
    }

    public function inspect() : InspectDataLayer
    {
        return InspectDataLayer::withRuntime($this->runtime);
    }
}