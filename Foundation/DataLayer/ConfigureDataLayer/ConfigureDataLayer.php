<?php

declare(strict_types=1);

namespace Avax\DataLayer\ConfigureDataLayer;

use Avax\DataLayer\AccelerateDataReads\AccelerateDataReads;
use Avax\DataLayer\AccessPersistentData\AccessPersistentData;
use Avax\DataLayer\AccessPersistentData\ExecuteRawDataQuery;
use Avax\DataLayer\AccessPersistentData\ReadPersistentData;
use Avax\DataLayer\AccessPersistentData\RunDataTransaction;
use Avax\DataLayer\AccessPersistentData\UseDatabaseRuntime;
use Avax\DataLayer\AccessPersistentData\WritePersistentData;
use Avax\DataLayer\CommitDataChanges\CommitDataChanges;
use Avax\DataLayer\CoordinateDataConsistency\CoordinateDataConsistency;
use Avax\DataLayer\DataLayer;
use Avax\DataLayer\DescribeStorageBehavior\DescribeStorageBehavior;
use Avax\DataLayer\DistributeStoredData\DistributeStoredData;
use Avax\DataLayer\EvolveStoredSchema\EvolveStoredSchema;
use Avax\DataLayer\InspectDataLayer\InspectDataLayer;
use Avax\DataLayer\OperateDataLayer\OperateDataLayer;
use Avax\DataLayer\PropagateDataChanges\PropagateDataChanges;
use Avax\DataLayer\ProtectStoredData\ProtectStoredData;
use Avax\DataLayer\QueryStoredData\QueryStoredData;
use Avax\DataLayer\ShapeStoredData\ShapeStoredData;
use Foundation\DataLayer\DataLayer;

/**
 * ConfigureDataLayer - composes DataLayer capabilities from explicit runtime dependencies.
 */
final readonly class ConfigureDataLayer
{
    public function __construct(
        private RegisterDataLayerRuntime $registerDataLayerRuntime = new RegisterDataLayerRuntime(),
        private ResolveDataLayerRuntime  $resolveDataLayerRuntime = new ResolveDataLayerRuntime()
    ) {}

    public function fromDatabaseRuntime(object $databaseRuntime) : DataLayer
    {
        return $this->configure(config: $this->registerDataLayerRuntime->register(databaseRuntime: $databaseRuntime));
    }

    /**
     * @throws DataLayerConfigurationFailure
     */
    public function configure(DataLayerConfig $config) : DataLayer
    {
        $runtime             = $this->resolveDataLayerRuntime->resolve(config: $config);
        $useDatabaseRuntime  = new UseDatabaseRuntime(runtime: $runtime);
        $executeRawDataQuery = new ExecuteRawDataQuery(useDatabaseRuntime: $useDatabaseRuntime);

        return new DataLayer(
            accessPersistentData     : new AccessPersistentData(
                                           useDatabaseRuntime : $useDatabaseRuntime,
                                           readPersistentData : new ReadPersistentData(executeRawDataQuery: $executeRawDataQuery),
                                           writePersistentData: new WritePersistentData(executeRawDataQuery: $executeRawDataQuery),
                                           executeRawDataQuery: $executeRawDataQuery,
                                           runDataTransaction : new RunDataTransaction(useDatabaseRuntime: $useDatabaseRuntime)
                                       ),
            shapeStoredData          : new ShapeStoredData(),
            evolveStoredSchema       : new EvolveStoredSchema(),
            queryStoredData          : new QueryStoredData(),
            commitDataChanges        : new CommitDataChanges(),
            describeStorageBehavior  : new DescribeStorageBehavior(),
            accelerateDataReads      : new AccelerateDataReads(),
            distributeStoredData     : new DistributeStoredData(),
            coordinateDataConsistency: new CoordinateDataConsistency(),
            propagateDataChanges     : new PropagateDataChanges(),
            protectStoredData        : new ProtectStoredData(),
            operateDataLayer         : new OperateDataLayer(),
            inspectDataLayer         : new InspectDataLayer()
        );
    }
}
