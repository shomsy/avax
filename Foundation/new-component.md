NOTE: Sve sto radis treba striktno da prati pravila iz AI Prompts foldera. Tacnije pravila iz how-to-*.md governance dokumenata. Ako pravila nisu jasna, prvo ih razjasni pre nego sto krenes sa kodom.

Ovo treba da ide kao **dva odvojena rada**, ali povezana:

```txt
Foundation/DataLayer
```

i

```txt
Foundation/ApplicationWorkflow/Saga
```

`DataLayer` poseduje data concerns: pristup podacima, schema, query, transakcije, consistency, CDC/outbox, security, operations. `Saga` ne ide unutra, jer je aplikacioni/workflow obrazac, ne database/data concern. Dokument koji smo tumačili eksplicitno odvaja database/data concerns od application consistency/workflow dela, gde pripadaju saga, retries, idempotency i compensating actions.

Plan ispod je pisan po tvojim how-to pravilima: folder mora da kaže flow ili capability, file/unit mora da kaže odgovornost, funkcija mora da kaže tačnu akciju. To je osnovni zakon iz architecture governance dokumenta.  Dokumentacija ide kroz `docs/` mirror, jer how-to-document kaže da je `docs/` jedina kanonska lokacija i da mora da mirroruje source strukturu.

---

# 1. Glavna odluka

Ne diraš agresivno postojeći:

```txt
Foundation/Database
```

On ostaje runtime komponenta za konkretan rad sa bazom.

Dodaješ:

```txt
Foundation/DataLayer
```

kao **širi data architecture umbrella**.

Dodaješ:

```txt
Foundation/ApplicationWorkflow/Saga
```

kao **workflow consistency component**.

Granica je ova:

```txt
DataLayer
- owns data access
- owns schema/modeling rules
- owns migrations and schema evolution rules
- owns query/transaction policy
- owns data consistency model
- owns CDC/outbox/event publication
- owns data security/governance
- owns operational data expectations

Saga
- owns long-running business workflow
- owns saga state
- owns steps
- owns compensation
- owns retries across systems
- owns command idempotency
- owns workflow recovery
```

Drugim rečima:

```txt
DataLayer zna kako se podaci čuvaju, čitaju, menjaju i propagiraju.
Saga zna kako poslovni proces preživljava neuspeh kroz više koraka.
```

To je čista granica. Ne mešati. ⚠️

---

# 2. Target source tree za DataLayer

Ovo bih uzeo kao kanonski source tree:

```txt
Foundation/
└── DataLayer/
    ├── DataLayer.php
    ├── DataLayerInterface.php
    │
    ├── ConfigureDataLayer/
    │   ├── ConfigureDataLayer.php
    │   ├── DataLayerConfig.php
    │   ├── RegisterDataLayerRuntime.php
    │   ├── ResolveDataLayerRuntime.php
    │   ├── ValidateDataLayerConfig.php
    │   ├── DataLayerRuntime.php
    │   └── DataLayerConfigurationFailure.php
    │
    ├── AccessPersistentData/
    │   ├── AccessPersistentData.php
    │   ├── UseDatabaseRuntime.php
    │   ├── ReadPersistentData.php
    │   ├── WritePersistentData.php
    │   ├── ExecuteRawDataQuery.php
    │   ├── RunDataTransaction.php
    │   ├── PersistentDataAccess.php
    │   ├── PersistentDataRequest.php
    │   ├── PersistentDataResult.php
    │   └── PersistentDataFailure.php
    │
    ├── ShapeStoredData/
    │   ├── ShapeStoredData.php
    │   ├── DescribeStoredModel.php
    │   ├── DescribeStoredField.php
    │   ├── DescribeStoredRelation.php
    │   ├── DescribeStoredConstraint.php
    │   ├── DescribeStoredIndex.php
    │   ├── DescribeTenantShape.php
    │   ├── StoredModel.php
    │   ├── StoredField.php
    │   ├── StoredRelation.php
    │   ├── StoredConstraint.php
    │   ├── StoredIndex.php
    │   └── TenantShape.php
    │
    ├── EvolveStoredSchema/
    │   ├── EvolveStoredSchema.php
    │   ├── PlanSchemaChange.php
    │   ├── VerifySchemaChange.php
    │   ├── ApplySchemaChange.php
    │   ├── RollbackSchemaChange.php
    │   ├── DetectSchemaDrift.php
    │   ├── DescribeSchemaRisk.php
    │   ├── DescribeZeroDowntimePath.php
    │   ├── SchemaChangePlan.php
    │   ├── SchemaChangeRisk.php
    │   ├── SchemaDriftReport.php
    │   └── ZeroDowntimeMigrationPlan.php
    │
    ├── QueryStoredData/
    │   ├── QueryStoredData.php
    │   ├── BuildDataQuery.php
    │   ├── ValidateDataQuery.php
    │   ├── CompileDataQuery.php
    │   ├── ExecuteDataQuery.php
    │   ├── ExplainDataQuery.php
    │   ├── DetectNPlusOneQuery.php
    │   ├── DataQuery.php
    │   ├── DataQueryPlan.php
    │   ├── DataQueryResult.php
    │   ├── NPlusOneQueryReport.php
    │   └── DataQueryFailure.php
    │
    ├── CommitDataChanges/
    │   ├── CommitDataChanges.php
    │   ├── OpenDataTransaction.php
    │   ├── CommitDataTransaction.php
    │   ├── RollbackDataTransaction.php
    │   ├── RetryTransientDataFailure.php
    │   ├── ChooseIsolationLevel.php
    │   ├── DetectDeadlock.php
    │   ├── CoordinateTwoPhaseCommit.php
    │   ├── PrepareDistributedCommit.php
    │   ├── CommitPreparedDataChange.php
    │   ├── AbortPreparedDataChange.php
    │   ├── DataTransaction.php
    │   ├── DataTransactionPolicy.php
    │   ├── IsolationLevel.php
    │   ├── TwoPhaseCommitPolicy.php
    │   └── DataTransactionFailure.php
    │
    ├── DescribeStorageBehavior/
    │   ├── DescribeStorageBehavior.php
    │   ├── DescribeWritePath.php
    │   ├── DescribeReadPath.php
    │   ├── DescribeRecoveryPath.php
    │   ├── DescribeIndexStorage.php
    │   ├── DescribeWriteAheadLog.php
    │   ├── DescribeBTreeStorage.php
    │   ├── DescribeLsmStorage.php
    │   ├── StorageBehavior.php
    │   ├── StorageEngineKind.php
    │   ├── WriteAheadLogBehavior.php
    │   ├── IndexStorageKind.php
    │   └── RecoveryGuarantee.php
    │
    ├── AccelerateDataReads/
    │   ├── AccelerateDataReads.php
    │   ├── ChooseReadProjection.php
    │   ├── ChooseDataIndex.php
    │   ├── UseMaterializedView.php
    │   ├── UseReadCache.php
    │   ├── InvalidateReadCache.php
    │   ├── UseBloomFilter.php
    │   ├── DescribeBloomFilter.php
    │   ├── DetectSlowDataQuery.php
    │   ├── ReadAccelerationPlan.php
    │   ├── ReadCachePolicy.php
    │   ├── MaterializedViewPolicy.php
    │   ├── BloomFilterPolicy.php
    │   └── SlowDataQueryReport.php
    │
    ├── DistributeStoredData/
    │   ├── DistributeStoredData.php
    │   ├── ChooseReadReplica.php
    │   ├── RouteToDataPartition.php
    │   ├── ChooseConsistentHashRing.php
    │   ├── RouteByConsistentHash.php
    │   ├── DescribeReplicationLag.php
    │   ├── DescribeShardKey.php
    │   ├── DataPartitionRoute.php
    │   ├── ReplicationPolicy.php
    │   ├── ReplicaLagReport.php
    │   ├── ShardKey.php
    │   └── ConsistentHashRing.php
    │
    ├── CoordinateDataConsistency/
    │   ├── CoordinateDataConsistency.php
    │   ├── DescribeConsistencyModel.php
    │   ├── RequireStrongConsistency.php
    │   ├── AllowEventualConsistency.php
    │   ├── ResolveDataConflict.php
    │   ├── DescribeQuorumPolicy.php
    │   ├── DescribeCapTradeoff.php
    │   ├── ConsistencyModel.php
    │   ├── ConflictResolution.php
    │   ├── QuorumPolicy.php
    │   └── CapTradeoff.php
    │
    ├── PropagateDataChanges/
    │   ├── PropagateDataChanges.php
    │   ├── RecordOutboxMessage.php
    │   ├── PublishOutboxMessage.php
    │   ├── ReadChangeStream.php
    │   ├── SyncDownstreamProjection.php
    │   ├── RetryChangePublication.php
    │   ├── DeduplicatePublishedChange.php
    │   ├── OutboxMessage.php
    │   ├── ChangeStreamCursor.php
    │   ├── DownstreamSyncResult.php
    │   ├── PublishedChange.php
    │   └── ChangePublicationFailure.php
    │
    ├── ProtectStoredData/
    │   ├── ProtectStoredData.php
    │   ├── RequireDataAccessPolicy.php
    │   ├── EnforceTenantBoundary.php
    │   ├── ClassifySensitiveField.php
    │   ├── MaskSensitiveData.php
    │   ├── RecordDataAuditTrail.php
    │   ├── DescribeRetentionPolicy.php
    │   ├── DataAccessPolicy.php
    │   ├── TenantBoundary.php
    │   ├── SensitiveField.php
    │   ├── DataAuditEntry.php
    │   └── RetentionPolicy.php
    │
    ├── OperateDataLayer/
    │   ├── OperateDataLayer.php
    │   ├── CheckDataLayerHealth.php
    │   ├── VerifyBackupPolicy.php
    │   ├── VerifyRestorePlan.php
    │   ├── PlanDataCapacity.php
    │   ├── DescribeFailoverPlan.php
    │   ├── VerifyMigrationSafety.php
    │   ├── DataLayerHealth.php
    │   ├── BackupPolicy.php
    │   ├── RestorePlan.php
    │   ├── CapacityPlan.php
    │   └── FailoverPlan.php
    │
    └── InspectDataLayer/
        ├── InspectDataLayer.php
        ├── RecordDataLayerEvent.php
        ├── MeasureDataQuery.php
        ├── FingerprintDataQuery.php
        ├── TraceDataTransaction.php
        ├── BuildDataLayerReport.php
        ├── DataLayerEvent.php
        ├── DataQueryMeasurement.php
        ├── DataQueryFingerprint.php
        ├── DataTransactionTrace.php
        └── DataLayerReport.php
```

Ovo pokriva sve sa slike, ali bez nasilnog stavljanja Sage u data komponentu.

---

# 3. Target source tree za Saga

Predlažem da Saga bude pod `ApplicationWorkflow`, ne direktno pored `DataLayer`, jer je Saga aplikacioni flow/workflow obrazac.

```txt
Foundation/
└── ApplicationWorkflow/
    ├── ApplicationWorkflow.php
    ├── ApplicationWorkflowInterface.php
    │
    └── Saga/
        ├── Saga.php
        ├── SagaInterface.php
        │
        ├── ConfigureSagaRuntime/
        │   ├── ConfigureSagaRuntime.php
        │   ├── SagaRuntimeConfig.php
        │   ├── RegisterSagaStore.php
        │   ├── RegisterSagaStepRunner.php
        │   ├── RegisterSagaMessageBus.php
        │   ├── ValidateSagaRuntimeConfig.php
        │   └── SagaRuntimeConfigurationFailure.php
        │
        ├── DefineSaga/
        │   ├── DefineSaga.php
        │   ├── RegisterSagaDefinition.php
        │   ├── ValidateSagaDefinition.php
        │   ├── DescribeSagaStep.php
        │   ├── DescribeSagaCompensation.php
        │   ├── SagaDefinition.php
        │   ├── SagaStepDefinition.php
        │   ├── SagaCompensationDefinition.php
        │   └── InvalidSagaDefinition.php
        │
        ├── StartSaga/
        │   ├── StartSaga.php
        │   ├── CreateSagaInstance.php
        │   ├── CreateSagaCorrelationId.php
        │   ├── RecordSagaStarted.php
        │   ├── ScheduleFirstSagaStep.php
        │   ├── SagaStartCommand.php
        │   ├── SagaInstance.php
        │   ├── SagaCorrelationId.php
        │   └── SagaStartFailure.php
        │
        ├── RunSagaStep/
        │   ├── RunSagaStep.php
        │   ├── LoadSagaInstance.php
        │   ├── ChooseNextSagaStep.php
        │   ├── ExecuteSagaStep.php
        │   ├── RecordSagaStepCompleted.php
        │   ├── RecordSagaStepFailed.php
        │   ├── ScheduleNextSagaStep.php
        │   ├── SagaStepResult.php
        │   ├── SagaStepFailure.php
        │   └── SagaStepExecutionPolicy.php
        │
        ├── CompleteSaga/
        │   ├── CompleteSaga.php
        │   ├── DetectSagaCompletion.php
        │   ├── RecordSagaCompleted.php
        │   ├── PublishSagaCompleted.php
        │   ├── SagaCompletion.php
        │   └── SagaCompletionFailure.php
        │
        ├── CompensateSaga/
        │   ├── CompensateSaga.php
        │   ├── ChooseCompensationSteps.php
        │   ├── ExecuteCompensationStep.php
        │   ├── RecordCompensationCompleted.php
        │   ├── RecordCompensationFailed.php
        │   ├── PublishSagaCompensated.php
        │   ├── CompensationPlan.php
        │   ├── CompensationStepResult.php
        │   └── SagaCompensationFailure.php
        │
        ├── ResumeSaga/
        │   ├── ResumeSaga.php
        │   ├── FindRecoverableSaga.php
        │   ├── RebuildSagaState.php
        │   ├── ContinueSagaAfterFailure.php
        │   ├── MarkSagaAsUnrecoverable.php
        │   ├── SagaRecoveryPlan.php
        │   └── SagaRecoveryFailure.php
        │
        ├── ProtectSagaIdempotency/
        │   ├── ProtectSagaIdempotency.php
        │   ├── DetectDuplicateSagaCommand.php
        │   ├── RecordSagaCommandKey.php
        │   ├── ReadPreviousSagaCommandResult.php
        │   ├── SagaCommandKey.php
        │   ├── SagaCommandResult.php
        │   └── DuplicateSagaCommand.php
        │
        ├── StoreSagaState/
        │   ├── StoreSagaState.php
        │   ├── SaveSagaState.php
        │   ├── ReadSagaState.php
        │   ├── AppendSagaEvent.php
        │   ├── ReadSagaEvents.php
        │   ├── SagaState.php
        │   ├── SagaEvent.php
        │   └── SagaStateStoreFailure.php
        │
        └── InspectSaga/
            ├── InspectSaga.php
            ├── RecordSagaEvent.php
            ├── BuildSagaTimeline.php
            ├── BuildSagaReport.php
            ├── TraceSagaFailure.php
            ├── SagaRuntimeEvent.php
            ├── SagaTimeline.php
            └── SagaReport.php
```

Ovo je mnogo bolje nego folder `SagaManager`, `WorkflowService`, `Handlers`, `Processors`, `Helpers`, jer svaka fascikla kaže konkretan tok ili capability.

---

# 4. Dokumentacioni mirror

Po `how-to-document` pravilima, dokumentacija ne treba da bude razbacana po source folderima. Kanonski ide ovako:

```txt
docs/
└── Foundation/
    ├── DataLayer/
    │   ├── how-this-works.md
    │   ├── DataLayer.md
    │   ├── DataLayerInterface.md
    │   ├── ConfigureDataLayer/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── AccessPersistentData/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── ShapeStoredData/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── EvolveStoredSchema/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── QueryStoredData/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── CommitDataChanges/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── DescribeStorageBehavior/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── AccelerateDataReads/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── DistributeStoredData/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── CoordinateDataConsistency/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── PropagateDataChanges/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── ProtectStoredData/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── OperateDataLayer/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   ├── InspectDataLayer/
    │   │   ├── how-this-works.md
    │   │   └── one-md-file-per-source-file.md
    │   └── Code-Review-And-ToDo/
    │       ├── review.md
    │       └── todo.md
    │
    └── ApplicationWorkflow/
        ├── how-this-works.md
        ├── ApplicationWorkflow.md
        ├── ApplicationWorkflowInterface.md
        └── Saga/
            ├── how-this-works.md
            ├── Saga.md
            ├── SagaInterface.md
            ├── ConfigureSagaRuntime/
            │   ├── how-this-works.md
            │   └── one-md-file-per-source-file.md
            ├── DefineSaga/
            │   ├── how-this-works.md
            │   └── one-md-file-per-source-file.md
            ├── StartSaga/
            │   ├── how-this-works.md
            │   └── one-md-file-per-source-file.md
            ├── RunSagaStep/
            │   ├── how-this-works.md
            │   └── one-md-file-per-source-file.md
            ├── CompleteSaga/
            │   ├── how-this-works.md
            │   └── one-md-file-per-source-file.md
            ├── CompensateSaga/
            │   ├── how-this-works.md
            │   └── one-md-file-per-source-file.md
            ├── ResumeSaga/
            │   ├── how-this-works.md
            │   └── one-md-file-per-source-file.md
            ├── ProtectSagaIdempotency/
            │   ├── how-this-works.md
            │   └── one-md-file-per-source-file.md
            ├── StoreSagaState/
            │   ├── how-this-works.md
            │   └── one-md-file-per-source-file.md
            ├── InspectSaga/
            │   ├── how-this-works.md
            │   └── one-md-file-per-source-file.md
            └── Code-Review-And-ToDo/
                ├── review.md
                └── todo.md
```

`one-md-file-per-source-file.md` nije bukvalan filename. To znači da svaki PHP file mora imati svoj `.md` mirror. Ako postoji `StartSaga.php`, mora da postoji:

```txt
docs/Foundation/ApplicationWorkflow/Saga/StartSaga/StartSaga.md
```

Bez preskakanja. How-to-document je tu strog: ako nešto postoji na disku, mora postojati i u dokumentaciji.

---

# 5. Plan rada, faze

## Phase 0: Governance lock

Cilj: pre nego što se piše kod, zaključati pravila.

ToDo:

```txt
[ ] DL-0001: Potvrditi da DataLayer nije zamena za Foundation/Database.
[ ] DL-0002: Potvrditi da Foundation/Database ostaje runtime komponenta.
[ ] DL-0003: Potvrditi da DataLayer sme da koristi Database, ali ne sme da kopira njegove interne klase.
[ ] DL-0004: Potvrditi da Saga ne ide u DataLayer.
[ ] DL-0005: Potvrditi da Saga ide u Foundation/ApplicationWorkflow/Saga.
[ ] DL-0006: Zabraniti foldere Services, Helpers, Utils, Common, Shared, Managers, Core.
[ ] DL-0007: Zabraniti nejasne klase tipa DataLayerManager, SagaManager, WorkflowProcessor.
[ ] DL-0008: Zaključati naming: folder = flow/capability, file = responsibility, method = exact action.
[ ] DL-0009: Zaključati docs mirror pod docs/Foundation/...
[ ] DL-0010: Zaključati da svaki ownership folder ima how-this-works.md u docs mirroru.
```

Acceptance criteria:

```txt
[ ] Postoji jedna jasna odluka: DataLayer owns data concerns.
[ ] Postoji jedna jasna odluka: Saga owns workflow consistency.
[ ] Nema duplog vlasništva između DataLayer/PropagateDataChanges i Saga/StoreSagaState.
[ ] Nema generic bucket foldera.
[ ] Nema source dokumentacije van docs mirror-a.
```

---

## Phase 1: Inventory postojećeg Database-a

Cilj: pre mapiranja moraš znati šta već imaš.

ToDo:

```txt
[ ] DL-0101: Popisati sve root facade-e u Foundation/Database.
[ ] DL-0102: Popisati Database capabilities: Connections, Query, ORM, Migrations, Transactions, Telemetry.
[ ] DL-0103: Popisati koji delovi Database-a mapiraju na DataLayer/AccessPersistentData.
[ ] DL-0104: Popisati koji delovi Database-a mapiraju na DataLayer/QueryStoredData.
[ ] DL-0105: Popisati koji delovi Database-a mapiraju na DataLayer/EvolveStoredSchema.
[ ] DL-0106: Popisati koji delovi Database-a mapiraju na DataLayer/CommitDataChanges.
[ ] DL-0107: Popisati koji delovi Database-a mapiraju na DataLayer/InspectDataLayer.
[ ] DL-0108: Obeležiti šta ne postoji: CDC, Outbox, governance, consistency, distribution, operations.
[ ] DL-0109: Obeležiti šta ne sme da se pomera sada.
[ ] DL-0110: Napraviti docs/Foundation/DataLayer/Code-Review-And-ToDo/review.md sa odlukom Keep and Improve.
```

Acceptance criteria:

```txt
[ ] Nijedan postojeći Database file nije fizički pomeren u ovoj fazi.
[ ] Postoji mapa current Database -> future DataLayer ownership.
[ ] Svaki mapping ima razlog.
[ ] Svaka praznina ima status: now / later / never.
```

---

## Phase 2: Napraviti DataLayer skeleton

Cilj: napraviti prazan, ali semantički ispravan oblik.

ToDo:

```txt
[ ] DL-0201: Kreirati Foundation/DataLayer.
[ ] DL-0202: Kreirati DataLayer.php kao mali public facade.
[ ] DL-0203: Kreirati DataLayerInterface.php samo ako zaista postoji korisnik interfejsa.
[ ] DL-0204: Kreirati ConfigureDataLayer folder.
[ ] DL-0205: Kreirati AccessPersistentData folder.
[ ] DL-0206: Kreirati ShapeStoredData folder.
[ ] DL-0207: Kreirati EvolveStoredSchema folder.
[ ] DL-0208: Kreirati QueryStoredData folder.
[ ] DL-0209: Kreirati CommitDataChanges folder.
[ ] DL-0210: Kreirati DescribeStorageBehavior folder.
[ ] DL-0211: Kreirati AccelerateDataReads folder.
[ ] DL-0212: Kreirati DistributeStoredData folder.
[ ] DL-0213: Kreirati CoordinateDataConsistency folder.
[ ] DL-0214: Kreirati PropagateDataChanges folder.
[ ] DL-0215: Kreirati ProtectStoredData folder.
[ ] DL-0216: Kreirati OperateDataLayer folder.
[ ] DL-0217: Kreirati InspectDataLayer folder.
[ ] DL-0218: U svaki folder dodati root owner file istog imena.
[ ] DL-0219: Ne dodavati implementacione detalje dok root owner nije jasan.
```

Acceptance criteria:

```txt
[ ] Svaki folder ima očigledan root owner.
[ ] Svaki root owner ima jednu rečenicu odgovornosti u PHPDoc-u.
[ ] Nijedan folder nije tehnički bucket.
[ ] DataLayer.php ne postaje god-object.
```

---

## Phase 3: DataLayer public surface

Cilj: definisati šta korisnik sme da vidi.

ToDo:

```txt
[ ] DL-0301: Definisati minimalni public API u DataLayer.php.
[ ] DL-0302: DataLayer.php sme da expose-uje samo high-level capability entry points.
[ ] DL-0303: Zabraniti da DataLayer.php expose-uje interne DTO detalje bez potrebe.
[ ] DL-0304: Definisati šta znači "persistent data access" u ovoj komponenti.
[ ] DL-0305: Definisati šta znači "schema evolution" u ovoj komponenti.
[ ] DL-0306: Definisati šta znači "change propagation" u ovoj komponenti.
[ ] DL-0307: Definisati šta znači "data protection" u ovoj komponenti.
[ ] DL-0308: Definisati šta DataLayer nikad ne radi.
```

Predlog public surface-a:

```php
final readonly class DataLayer
{
    public function access(): AccessPersistentData;

    public function shape(): ShapeStoredData;

    public function evolveSchema(): EvolveStoredSchema;

    public function query(): QueryStoredData;

    public function commitChanges(): CommitDataChanges;

    public function propagateChanges(): PropagateDataChanges;

    public function protectData(): ProtectStoredData;

    public function inspect(): InspectDataLayer;
}
```

Ne bih odmah expose-ovao sve. `DescribeStorageBehavior`, `AccelerateDataReads`, `DistributeStoredData`, `CoordinateDataConsistency`, `OperateDataLayer` mogu ostati dostupni kroz config, docs ili advanced API dok ne postoji stvarna potreba.

Acceptance criteria:

```txt
[ ] Public API je mali.
[ ] API ne skriva SQL realnost.
[ ] API ne forsira ORM kao jedini put.
[ ] Raw query escape hatch je predviđen.
[ ] Transaction API je eksplicitan.
[ ] Nema magic ponašanja.
```

---

# 6. DataLayer ToDo po slice-u

## ConfigureDataLayer

Purpose: sastavlja DataLayer i povezuje ga sa postojećim Database runtime-om.

ToDo:

```txt
[ ] DL-CFG-001: Napraviti DataLayerConfig kao readonly config object.
[ ] DL-CFG-002: Napraviti DataLayerRuntime kao value object za povezane runtime dependencies.
[ ] DL-CFG-003: Napraviti RegisterDataLayerRuntime.
[ ] DL-CFG-004: Napraviti ResolveDataLayerRuntime.
[ ] DL-CFG-005: Napraviti ValidateDataLayerConfig.
[ ] DL-CFG-006: Definisati koje zavisnosti dolaze iz Foundation/Database.
[ ] DL-CFG-007: Zabraniti hard-coded global access na Database.
[ ] DL-CFG-008: Dodati jasne exception-e za missing runtime.
[ ] DL-CFG-009: Dodati test za invalid config.
[ ] DL-CFG-010: Dodati test za valid config.
```

Acceptance criteria:

```txt
[ ] DataLayer se ne sastavlja sam iz global state-a.
[ ] Database runtime dependency je eksplicitna.
[ ] Invalid config failuje rano.
[ ] Error message kaže šta fali i kako se popravlja.
```

---

## AccessPersistentData

Purpose: aplikacioni ulaz za čitanje/pisanje bez vezivanja korisnika za direktan Database internals.

ToDo:

```txt
[ ] DL-APD-001: Napraviti AccessPersistentData root owner.
[ ] DL-APD-002: Napraviti UseDatabaseRuntime kao jedinu dozvoljenu tačku ka Foundation/Database.
[ ] DL-APD-003: Napraviti ReadPersistentData.
[ ] DL-APD-004: Napraviti WritePersistentData.
[ ] DL-APD-005: Napraviti ExecuteRawDataQuery.
[ ] DL-APD-006: Napraviti RunDataTransaction.
[ ] DL-APD-007: Napraviti PersistentDataRequest.
[ ] DL-APD-008: Napraviti PersistentDataResult.
[ ] DL-APD-009: Napraviti PersistentDataFailure.
[ ] DL-APD-010: Osigurati da raw SQL koristi named params.
[ ] DL-APD-011: Osigurati da result mapping bude eksplicitan.
[ ] DL-APD-012: Dodati test da raw SQL ne prolazi bez param binding-a.
[ ] DL-APD-013: Dodati test za read path.
[ ] DL-APD-014: Dodati test za write path.
[ ] DL-APD-015: Dodati test za transaction handoff.
```

Acceptance criteria:

```txt
[ ] Nema direktnog curenja Database internals u aplikacioni kod.
[ ] Raw SQL je first-class, ne "poraz".
[ ] Transactions su eksplicitne.
[ ] Read/write razlika je jasna.
```

---

## ShapeStoredData

Purpose: opisuje logički oblik podataka: model, field, relation, constraints, index, tenancy.

ToDo:

```txt
[ ] DL-SSD-001: Napraviti StoredModel.
[ ] DL-SSD-002: Napraviti StoredField.
[ ] DL-SSD-003: Napraviti StoredRelation.
[ ] DL-SSD-004: Napraviti StoredConstraint.
[ ] DL-SSD-005: Napraviti StoredIndex.
[ ] DL-SSD-006: Napraviti TenantShape.
[ ] DL-SSD-007: Napraviti DescribeStoredModel.
[ ] DL-SSD-008: Napraviti DescribeStoredField.
[ ] DL-SSD-009: Napraviti DescribeStoredRelation.
[ ] DL-SSD-010: Napraviti DescribeStoredConstraint.
[ ] DL-SSD-011: Napraviti DescribeStoredIndex.
[ ] DL-SSD-012: Napraviti DescribeTenantShape.
[ ] DL-SSD-013: Podržati primary key.
[ ] DL-SSD-014: Podržati foreign key.
[ ] DL-SSD-015: Podržati unique constraint.
[ ] DL-SSD-016: Podržati check constraint.
[ ] DL-SSD-017: Podržati partial index kao opis, ne odmah kao izvršenje.
[ ] DL-SSD-018: Podržati composite key kao opis.
[ ] DL-SSD-019: Podržati generated/computed field kao opis.
[ ] DL-SSD-020: Dodati validaciju da model bez identiteta mora biti eksplicitno označen.
```

Acceptance criteria:

```txt
[ ] Schema nije implicitna.
[ ] Index nije samo string.
[ ] Constraint nije samo komentar.
[ ] Tenant boundary postoji kao model-level concept.
[ ] Denormalizacija mora biti namerno opisana.
```

---

## EvolveStoredSchema

Purpose: schema change nije “pusti migration pa šta bude”, nego plan, verification, risk, apply, rollback.

ToDo:

```txt
[ ] DL-ESS-001: Napraviti SchemaChangePlan.
[ ] DL-ESS-002: Napraviti SchemaChangeRisk.
[ ] DL-ESS-003: Napraviti SchemaDriftReport.
[ ] DL-ESS-004: Napraviti ZeroDowntimeMigrationPlan.
[ ] DL-ESS-005: Napraviti PlanSchemaChange.
[ ] DL-ESS-006: Napraviti VerifySchemaChange.
[ ] DL-ESS-007: Napraviti ApplySchemaChange.
[ ] DL-ESS-008: Napraviti RollbackSchemaChange.
[ ] DL-ESS-009: Napraviti DetectSchemaDrift.
[ ] DL-ESS-010: Napraviti DescribeSchemaRisk.
[ ] DL-ESS-011: Napraviti DescribeZeroDowntimePath.
[ ] DL-ESS-012: Prepoznati destructive change.
[ ] DL-ESS-013: Prepoznati nullable -> non-nullable rizik.
[ ] DL-ESS-014: Prepoznati index creation lock rizik.
[ ] DL-ESS-015: Prepoznati table rewrite rizik.
[ ] DL-ESS-016: Prepoznati migration bez rollback plana.
[ ] DL-ESS-017: Dodati dry-run mode.
[ ] DL-ESS-018: Dodati migration verification report.
[ ] DL-ESS-019: Dodati test za destructive migration detection.
[ ] DL-ESS-020: Dodati test za drift detection.
```

Acceptance criteria:

```txt
[ ] Migration nikad nije igra na sreću.
[ ] Svaka schema promena ima risk report.
[ ] Data loss rizik je eksplicitan.
[ ] Rollback je ili definisan ili jasno označen kao unsupported.
```

---

## QueryStoredData

Purpose: query path mora biti inspectable, explainable i testable.

ToDo:

```txt
[ ] DL-QSD-001: Napraviti DataQuery.
[ ] DL-QSD-002: Napraviti DataQueryPlan.
[ ] DL-QSD-003: Napraviti DataQueryResult.
[ ] DL-QSD-004: Napraviti DataQueryFailure.
[ ] DL-QSD-005: Napraviti BuildDataQuery.
[ ] DL-QSD-006: Napraviti ValidateDataQuery.
[ ] DL-QSD-007: Napraviti CompileDataQuery.
[ ] DL-QSD-008: Napraviti ExecuteDataQuery.
[ ] DL-QSD-009: Napraviti ExplainDataQuery.
[ ] DL-QSD-010: Napraviti DetectNPlusOneQuery.
[ ] DL-QSD-011: Omogućiti typed projections gde PHP može realno da pomogne.
[ ] DL-QSD-012: Omogućiti raw expressions kao eksplicitan concept.
[ ] DL-QSD-013: Omogućiti query fingerprinting handoff ka InspectDataLayer.
[ ] DL-QSD-014: Dodati test za invalid field.
[ ] DL-QSD-015: Dodati test za invalid relation.
[ ] DL-QSD-016: Dodati test za projection-only query.
[ ] DL-QSD-017: Dodati test za explain query path.
[ ] DL-QSD-018: Dodati test za N+1 detection scenario.
```

Acceptance criteria:

```txt
[ ] Query može da se objasni pre izvršavanja.
[ ] Query može da se fingerprintuje.
[ ] Query result ne mora da hidrira ceo object.
[ ] N+1 problem se vidi, ne krije.
```

---

## CommitDataChanges

Purpose: transakcije, isolation, retry, deadlock, 2PC ako baš treba.

ToDo:

```txt
[ ] DL-CDC-001: Napraviti DataTransaction.
[ ] DL-CDC-002: Napraviti DataTransactionPolicy.
[ ] DL-CDC-003: Napraviti IsolationLevel enum.
[ ] DL-CDC-004: Napraviti TwoPhaseCommitPolicy.
[ ] DL-CDC-005: Napraviti OpenDataTransaction.
[ ] DL-CDC-006: Napraviti CommitDataTransaction.
[ ] DL-CDC-007: Napraviti RollbackDataTransaction.
[ ] DL-CDC-008: Napraviti RetryTransientDataFailure.
[ ] DL-CDC-009: Napraviti ChooseIsolationLevel.
[ ] DL-CDC-010: Napraviti DetectDeadlock.
[ ] DL-CDC-011: Napraviti CoordinateTwoPhaseCommit.
[ ] DL-CDC-012: Napraviti PrepareDistributedCommit.
[ ] DL-CDC-013: Napraviti CommitPreparedDataChange.
[ ] DL-CDC-014: Napraviti AbortPreparedDataChange.
[ ] DL-CDC-015: Definisati retry policy samo za idempotentne operacije.
[ ] DL-CDC-016: Zabraniti retry koji ponavlja ne-idempotentni side effect.
[ ] DL-CDC-017: Dodati test za rollback.
[ ] DL-CDC-018: Dodati test za deadlock retry.
[ ] DL-CDC-019: Dodati test za non-retryable failure.
[ ] DL-CDC-020: Dokumentovati da 2PC nije default.
```

Acceptance criteria:

```txt
[ ] Transaction boundary je eksplicitan.
[ ] Isolation level je vidljiv.
[ ] Retry ne pravi duple side effect-e.
[ ] 2PC postoji kao advanced policy, ne kao default.
```

---

## DescribeStorageBehavior

Purpose: ne implementira storage engine, nego dokumentuje i modeluje pretpostavke koje utiču na dizajn.

ToDo:

```txt
[ ] DL-DSB-001: Napraviti StorageBehavior.
[ ] DL-DSB-002: Napraviti StorageEngineKind enum.
[ ] DL-DSB-003: Napraviti WriteAheadLogBehavior.
[ ] DL-DSB-004: Napraviti IndexStorageKind.
[ ] DL-DSB-005: Napraviti RecoveryGuarantee.
[ ] DL-DSB-006: Napraviti DescribeWritePath.
[ ] DL-DSB-007: Napraviti DescribeReadPath.
[ ] DL-DSB-008: Napraviti DescribeRecoveryPath.
[ ] DL-DSB-009: Napraviti DescribeIndexStorage.
[ ] DL-DSB-010: Napraviti DescribeWriteAheadLog.
[ ] DL-DSB-011: Napraviti DescribeBTreeStorage.
[ ] DL-DSB-012: Napraviti DescribeLsmStorage.
[ ] DL-DSB-013: Dokumentovati da ovo nije custom database engine.
[ ] DL-DSB-014: Dodati examples za MySQL/PostgreSQL/SQLite assumptions.
```

Acceptance criteria:

```txt
[ ] Tim zna storage pretpostavke.
[ ] WAL, B-Tree, LSM nisu samo buzzwords.
[ ] Ovaj folder ne pokušava da implementira bazu.
```

---

## AccelerateDataReads

Purpose: indexes, cache, materialized views, bloom filters, projections.

ToDo:

```txt
[ ] DL-ADR-001: Napraviti ReadAccelerationPlan.
[ ] DL-ADR-002: Napraviti ReadCachePolicy.
[ ] DL-ADR-003: Napraviti MaterializedViewPolicy.
[ ] DL-ADR-004: Napraviti BloomFilterPolicy.
[ ] DL-ADR-005: Napraviti SlowDataQueryReport.
[ ] DL-ADR-006: Napraviti ChooseReadProjection.
[ ] DL-ADR-007: Napraviti ChooseDataIndex.
[ ] DL-ADR-008: Napraviti UseMaterializedView.
[ ] DL-ADR-009: Napraviti UseReadCache.
[ ] DL-ADR-010: Napraviti InvalidateReadCache.
[ ] DL-ADR-011: Napraviti UseBloomFilter.
[ ] DL-ADR-012: Napraviti DescribeBloomFilter.
[ ] DL-ADR-013: Napraviti DetectSlowDataQuery.
[ ] DL-ADR-014: Definisati cache invalidation kao obavezan deo cache policy-ja.
[ ] DL-ADR-015: Definisati refresh strategy za materialized view.
[ ] DL-ADR-016: Dodati test da cache bez invalidation policy-ja failuje.
[ ] DL-ADR-017: Dodati test za slow query threshold.
```

Acceptance criteria:

```txt
[ ] System nije magični performance flaster.
[ ] Index se bira na osnovu query pattern-a.
[ ] Materialized view ima refresh strategy.
[ ] Bloom filter ima jasan use-case.
```

---

## DistributeStoredData

Purpose: replication, read replicas, sharding, partition routing, consistent hashing.

ToDo:

```txt
[ ] DL-DSD-001: Napraviti DataPartitionRoute.
[ ] DL-DSD-002: Napraviti ReplicationPolicy.
[ ] DL-DSD-003: Napraviti ReplicaLagReport.
[ ] DL-DSD-004: Napraviti ShardKey.
[ ] DL-DSD-005: Napraviti ConsistentHashRing.
[ ] DL-DSD-006: Napraviti ChooseReadReplica.
[ ] DL-DSD-007: Napraviti RouteToDataPartition.
[ ] DL-DSD-008: Napraviti ChooseConsistentHashRing.
[ ] DL-DSD-009: Napraviti RouteByConsistentHash.
[ ] DL-DSD-010: Napraviti DescribeReplicationLag.
[ ] DL-DSD-011: Napraviti DescribeShardKey.
[ ] DL-DSD-012: Definisati read-after-write expectation.
[ ] DL-DSD-013: Definisati replica lag visibility.
[ ] DL-DSD-014: Definisati deterministic shard routing.
[ ] DL-DSD-015: Dodati test za shard key determinism.
[ ] DL-DSD-016: Dodati test za replica lag warning.
```

Acceptance criteria:

```txt
[ ] Read replica nije nevidljiva slučajnost.
[ ] Shard key je dokumentovan.
[ ] Partition route je deterministic.
[ ] Consistent hashing je eksplicitan, ne sakriven u helperu.
```

---

## CoordinateDataConsistency

Purpose: strong/eventual consistency, CAP tradeoff, quorum, conflict resolution.

ToDo:

```txt
[ ] DL-CONS-001: Napraviti ConsistencyModel.
[ ] DL-CONS-002: Napraviti ConflictResolution.
[ ] DL-CONS-003: Napraviti QuorumPolicy.
[ ] DL-CONS-004: Napraviti CapTradeoff.
[ ] DL-CONS-005: Napraviti DescribeConsistencyModel.
[ ] DL-CONS-006: Napraviti RequireStrongConsistency.
[ ] DL-CONS-007: Napraviti AllowEventualConsistency.
[ ] DL-CONS-008: Napraviti ResolveDataConflict.
[ ] DL-CONS-009: Napraviti DescribeQuorumPolicy.
[ ] DL-CONS-010: Napraviti DescribeCapTradeoff.
[ ] DL-CONS-011: Definisati per-workflow consistency requirement.
[ ] DL-CONS-012: Definisati conflict resolution kao deterministic.
[ ] DL-CONS-013: Dokumentovati da CAP nije klasa koja "radi nešto", nego design decision.
[ ] DL-CONS-014: Dodati test za eventual consistency acceptance.
[ ] DL-CONS-015: Dodati test za conflict resolution.
```

Acceptance criteria:

```txt
[ ] Consistency nije podrazumevana.
[ ] Eventual consistency je vidljiva proizvodu.
[ ] Conflict resolution je deterministic.
[ ] CAP tradeoff je dokumentovan kao odluka.
```

---

## PropagateDataChanges

Purpose: outbox, CDC, event publication, downstream sync.

ToDo:

```txt
[ ] DL-PDC-001: Napraviti OutboxMessage.
[ ] DL-PDC-002: Napraviti ChangeStreamCursor.
[ ] DL-PDC-003: Napraviti DownstreamSyncResult.
[ ] DL-PDC-004: Napraviti PublishedChange.
[ ] DL-PDC-005: Napraviti ChangePublicationFailure.
[ ] DL-PDC-006: Napraviti RecordOutboxMessage.
[ ] DL-PDC-007: Napraviti PublishOutboxMessage.
[ ] DL-PDC-008: Napraviti ReadChangeStream.
[ ] DL-PDC-009: Napraviti SyncDownstreamProjection.
[ ] DL-PDC-010: Napraviti RetryChangePublication.
[ ] DL-PDC-011: Napraviti DeduplicatePublishedChange.
[ ] DL-PDC-012: Definisati event id.
[ ] DL-PDC-013: Definisati idempotency key za published change.
[ ] DL-PDC-014: Definisati ordering guarantees.
[ ] DL-PDC-015: Definisati reprocessing behavior.
[ ] DL-PDC-016: Dodati test za duplicate event.
[ ] DL-PDC-017: Dodati test za outbox record inside transaction.
[ ] DL-PDC-018: Dodati test za failed publication retry.
```

Acceptance criteria:

```txt
[ ] Event publication je reliable.
[ ] Outbox se piše u istoj transakciji kao data change.
[ ] Downstream consumer može da dobije duplikat bez pucanja.
[ ] Reprocessing je moguć.
```

---

## ProtectStoredData

Purpose: tenant boundary, policy, masking, audit, retention.

ToDo:

```txt
[ ] DL-PSD-001: Napraviti DataAccessPolicy.
[ ] DL-PSD-002: Napraviti TenantBoundary.
[ ] DL-PSD-003: Napraviti SensitiveField.
[ ] DL-PSD-004: Napraviti DataAuditEntry.
[ ] DL-PSD-005: Napraviti RetentionPolicy.
[ ] DL-PSD-006: Napraviti RequireDataAccessPolicy.
[ ] DL-PSD-007: Napraviti EnforceTenantBoundary.
[ ] DL-PSD-008: Napraviti ClassifySensitiveField.
[ ] DL-PSD-009: Napraviti MaskSensitiveData.
[ ] DL-PSD-010: Napraviti RecordDataAuditTrail.
[ ] DL-PSD-011: Napraviti DescribeRetentionPolicy.
[ ] DL-PSD-012: Zabraniti UI-only authorization.
[ ] DL-PSD-013: Zabraniti tenant filter kao "disciplina programera".
[ ] DL-PSD-014: Dodati test za tenant boundary.
[ ] DL-PSD-015: Dodati test za masked field.
[ ] DL-PSD-016: Dodati test za audit entry.
```

Acceptance criteria:

```txt
[ ] Tenant boundary je sistemski enforced.
[ ] Sensitive field ne curi u logs/traces.
[ ] Audit trail je pouzdan.
[ ] Retention policy je eksplicitna.
```

---

## OperateDataLayer

Purpose: health, backup, restore, capacity, failover, migration safety.

ToDo:

```txt
[ ] DL-ODL-001: Napraviti DataLayerHealth.
[ ] DL-ODL-002: Napraviti BackupPolicy.
[ ] DL-ODL-003: Napraviti RestorePlan.
[ ] DL-ODL-004: Napraviti CapacityPlan.
[ ] DL-ODL-005: Napraviti FailoverPlan.
[ ] DL-ODL-006: Napraviti CheckDataLayerHealth.
[ ] DL-ODL-007: Napraviti VerifyBackupPolicy.
[ ] DL-ODL-008: Napraviti VerifyRestorePlan.
[ ] DL-ODL-009: Napraviti PlanDataCapacity.
[ ] DL-ODL-010: Napraviti DescribeFailoverPlan.
[ ] DL-ODL-011: Napraviti VerifyMigrationSafety.
[ ] DL-ODL-012: Definisati RPO.
[ ] DL-ODL-013: Definisati RTO.
[ ] DL-ODL-014: Definisati restore drill expectation.
[ ] DL-ODL-015: Dodati test za health report.
```

Acceptance criteria:

```txt
[ ] Backup nije samo konfigurisan, nego proverljiv.
[ ] Restore plan je dokumentovan.
[ ] Migration safety ima gate.
[ ] Health report daje actionable rezultat.
```

---

## InspectDataLayer

Purpose: observability, metrics, query fingerprint, transaction timeline.

ToDo:

```txt
[ ] DL-IDL-001: Napraviti DataLayerEvent.
[ ] DL-IDL-002: Napraviti DataQueryMeasurement.
[ ] DL-IDL-003: Napraviti DataQueryFingerprint.
[ ] DL-IDL-004: Napraviti DataTransactionTrace.
[ ] DL-IDL-005: Napraviti DataLayerReport.
[ ] DL-IDL-006: Napraviti RecordDataLayerEvent.
[ ] DL-IDL-007: Napraviti MeasureDataQuery.
[ ] DL-IDL-008: Napraviti FingerprintDataQuery.
[ ] DL-IDL-009: Napraviti TraceDataTransaction.
[ ] DL-IDL-010: Napraviti BuildDataLayerReport.
[ ] DL-IDL-011: Definisati slow query threshold.
[ ] DL-IDL-012: Definisati sensitive-data-safe logging.
[ ] DL-IDL-013: Definisati correlation id handoff.
[ ] DL-IDL-014: Dodati test da sensitive values nisu u event payload-u.
```

Acceptance criteria:

```txt
[ ] Query može da se izmeri.
[ ] Query može da se fingerprintuje.
[ ] Transaction timeline može da se rekonstruiše.
[ ] Logs/traces ne cure sensitive data.
```

---

# 7. Saga plan

Saga mora biti workflow sistem, ne data sistem.

DataLayer daje:

```txt
- transaction
- outbox
- state store
- event publication
- consistency policies
```

Saga koristi te stvari, ali ih ne poseduje.

## Phase S0: Saga boundary lock

ToDo:

```txt
[ ] SG-0001: Potvrditi da Saga živi u Foundation/ApplicationWorkflow/Saga.
[ ] SG-0002: Potvrditi da Saga ne ulazi u Foundation/DataLayer.
[ ] SG-0003: Potvrditi da Saga sme da koristi DataLayer/PropagateDataChanges za outbox.
[ ] SG-0004: Potvrditi da Saga sme da koristi DataLayer/CommitDataChanges za transaction boundary.
[ ] SG-0005: Potvrditi da Saga poseduje compensation, retry, recovery, idempotency.
[ ] SG-0006: Zabraniti SagaManager, WorkflowProcessor, CommonHandlers.
[ ] SG-0007: Zaključati da svaki saga flow ima root owner file.
```

Acceptance criteria:

```txt
[ ] Saga nije database component.
[ ] Saga nije message bus.
[ ] Saga nije generic workflow engine za sve moguće stvari.
[ ] Saga je jasan pattern za long-running business process.
```

---

## Phase S1: Saga skeleton

ToDo:

```txt
[ ] SG-0101: Kreirati Foundation/ApplicationWorkflow.
[ ] SG-0102: Kreirati ApplicationWorkflow.php.
[ ] SG-0103: Kreirati ApplicationWorkflowInterface.php samo ako postoji realan consumer.
[ ] SG-0104: Kreirati Foundation/ApplicationWorkflow/Saga.
[ ] SG-0105: Kreirati Saga.php kao mali public facade.
[ ] SG-0106: Kreirati SagaInterface.php samo ako postoji realan consumer.
[ ] SG-0107: Kreirati ConfigureSagaRuntime.
[ ] SG-0108: Kreirati DefineSaga.
[ ] SG-0109: Kreirati StartSaga.
[ ] SG-0110: Kreirati RunSagaStep.
[ ] SG-0111: Kreirati CompleteSaga.
[ ] SG-0112: Kreirati CompensateSaga.
[ ] SG-0113: Kreirati ResumeSaga.
[ ] SG-0114: Kreirati ProtectSagaIdempotency.
[ ] SG-0115: Kreirati StoreSagaState.
[ ] SG-0116: Kreirati InspectSaga.
```

Acceptance criteria:

```txt
[ ] Svaki folder ima konkretan owner.
[ ] Nema generic "Handlers".
[ ] Nema hidden orchestration.
[ ] Saga.php ostaje mali public facade.
```

---

## ConfigureSagaRuntime

Purpose: sastavlja runtime dependencies za Saga.

ToDo:

```txt
[ ] SG-CFG-001: Napraviti SagaRuntimeConfig.
[ ] SG-CFG-002: Napraviti RegisterSagaStore.
[ ] SG-CFG-003: Napraviti RegisterSagaStepRunner.
[ ] SG-CFG-004: Napraviti RegisterSagaMessageBus.
[ ] SG-CFG-005: Napraviti ValidateSagaRuntimeConfig.
[ ] SG-CFG-006: Napraviti SagaRuntimeConfigurationFailure.
[ ] SG-CFG-007: Definisati required dependencies: store, clock, message bus, logger/event recorder.
[ ] SG-CFG-008: Definisati optional dependencies: retry policy, timeout policy, tracing.
[ ] SG-CFG-009: Dodati test za missing saga store.
[ ] SG-CFG-010: Dodati test za invalid runtime config.
```

Acceptance criteria:

```txt
[ ] Saga runtime se ne sastavlja iz global state-a.
[ ] Missing store failuje rano.
[ ] Missing step runner failuje rano.
[ ] Error message je upotrebljiv.
```

---

## DefineSaga

Purpose: opisuje šta Saga jeste pre izvršenja.

ToDo:

```txt
[ ] SG-DEF-001: Napraviti SagaDefinition.
[ ] SG-DEF-002: Napraviti SagaStepDefinition.
[ ] SG-DEF-003: Napraviti SagaCompensationDefinition.
[ ] SG-DEF-004: Napraviti DefineSaga.
[ ] SG-DEF-005: Napraviti RegisterSagaDefinition.
[ ] SG-DEF-006: Napraviti ValidateSagaDefinition.
[ ] SG-DEF-007: Napraviti DescribeSagaStep.
[ ] SG-DEF-008: Napraviti DescribeSagaCompensation.
[ ] SG-DEF-009: Napraviti InvalidSagaDefinition.
[ ] SG-DEF-010: Zabraniti step bez name-a.
[ ] SG-DEF-011: Zabraniti compensation za step koji nema side effect, osim ako je eksplicitno justified.
[ ] SG-DEF-012: Zabraniti duple step name-ove.
[ ] SG-DEF-013: Zabraniti nepoznat next step.
[ ] SG-DEF-014: Dodati test za invalid definition.
[ ] SG-DEF-015: Dodati test za valid linear saga.
[ ] SG-DEF-016: Dodati test za valid branching saga ako podržiš branching.
```

Acceptance criteria:

```txt
[ ] Saga definition je validirana pre runtime-a.
[ ] Step order je jasan.
[ ] Compensation je deo definition-a, ne afterthought.
[ ] Invalid state je teško napraviti.
```

---

## StartSaga

Purpose: pravi novu saga instancu i zakazuje prvi korak.

ToDo:

```txt
[ ] SG-START-001: Napraviti SagaStartCommand.
[ ] SG-START-002: Napraviti SagaInstance.
[ ] SG-START-003: Napraviti SagaCorrelationId.
[ ] SG-START-004: Napraviti SagaStartFailure.
[ ] SG-START-005: Napraviti StartSaga.
[ ] SG-START-006: Napraviti CreateSagaInstance.
[ ] SG-START-007: Napraviti CreateSagaCorrelationId.
[ ] SG-START-008: Napraviti RecordSagaStarted.
[ ] SG-START-009: Napraviti ScheduleFirstSagaStep.
[ ] SG-START-010: Osigurati da start bude idempotentan po command key-u ako je zadat.
[ ] SG-START-011: Osigurati da correlation id bude obavezan ili deterministički generisan.
[ ] SG-START-012: Dodati test za successful start.
[ ] SG-START-013: Dodati test za duplicate start command.
[ ] SG-START-014: Dodati test za invalid saga definition.
```

Acceptance criteria:

```txt
[ ] Start ostavlja state.
[ ] Start ostavlja event.
[ ] Start zakazuje prvi step.
[ ] Duplicate command ne pravi duplu sagu.
```

---

## RunSagaStep

Purpose: izvršava jedan konkretan korak i odlučuje šta dalje.

ToDo:

```txt
[ ] SG-RUN-001: Napraviti SagaStepResult.
[ ] SG-RUN-002: Napraviti SagaStepFailure.
[ ] SG-RUN-003: Napraviti SagaStepExecutionPolicy.
[ ] SG-RUN-004: Napraviti RunSagaStep.
[ ] SG-RUN-005: Napraviti LoadSagaInstance.
[ ] SG-RUN-006: Napraviti ChooseNextSagaStep.
[ ] SG-RUN-007: Napraviti ExecuteSagaStep.
[ ] SG-RUN-008: Napraviti RecordSagaStepCompleted.
[ ] SG-RUN-009: Napraviti RecordSagaStepFailed.
[ ] SG-RUN-010: Napraviti ScheduleNextSagaStep.
[ ] SG-RUN-011: Osigurati da step execution bude idempotentno za isti saga step attempt.
[ ] SG-RUN-012: Osigurati da failed step ne nestane bez event-a.
[ ] SG-RUN-013: Osigurati da next step ne može biti unknown.
[ ] SG-RUN-014: Dodati test za successful step.
[ ] SG-RUN-015: Dodati test za failed step.
[ ] SG-RUN-016: Dodati test za retryable failure.
[ ] SG-RUN-017: Dodati test za non-retryable failure.
```

Acceptance criteria:

```txt
[ ] Svaki step ima state transition.
[ ] Svaki failure ostavlja trag.
[ ] Next step decision je izolovan.
[ ] Step runner ne zna storage detalje.
```

---

## CompleteSaga

Purpose: zatvara sagu kada više nema koraka.

ToDo:

```txt
[ ] SG-COMPLETE-001: Napraviti SagaCompletion.
[ ] SG-COMPLETE-002: Napraviti SagaCompletionFailure.
[ ] SG-COMPLETE-003: Napraviti CompleteSaga.
[ ] SG-COMPLETE-004: Napraviti DetectSagaCompletion.
[ ] SG-COMPLETE-005: Napraviti RecordSagaCompleted.
[ ] SG-COMPLETE-006: Napraviti PublishSagaCompleted.
[ ] SG-COMPLETE-007: Osigurati da completed saga ne može ponovo da izvrši step.
[ ] SG-COMPLETE-008: Osigurati da completion event ide kroz outbox.
[ ] SG-COMPLETE-009: Dodati test za successful completion.
[ ] SG-COMPLETE-010: Dodati test za duplicate completion.
```

Acceptance criteria:

```txt
[ ] Completed je terminal state.
[ ] Completion je publishable event.
[ ] Duplicate completion je safe.
```

---

## CompensateSaga

Purpose: vraća ili neutralizuje posledice prethodnih uspešnih koraka.

ToDo:

```txt
[ ] SG-COMP-001: Napraviti CompensationPlan.
[ ] SG-COMP-002: Napraviti CompensationStepResult.
[ ] SG-COMP-003: Napraviti SagaCompensationFailure.
[ ] SG-COMP-004: Napraviti CompensateSaga.
[ ] SG-COMP-005: Napraviti ChooseCompensationSteps.
[ ] SG-COMP-006: Napraviti ExecuteCompensationStep.
[ ] SG-COMP-007: Napraviti RecordCompensationCompleted.
[ ] SG-COMP-008: Napraviti RecordCompensationFailed.
[ ] SG-COMP-009: Napraviti PublishSagaCompensated.
[ ] SG-COMP-010: Compensation order mora biti obrnut od successful side-effect steps.
[ ] SG-COMP-011: Compensation mora biti idempotentna.
[ ] SG-COMP-012: Failed compensation mora ostaviti unrecoverable ili recoverable state.
[ ] SG-COMP-013: Dodati test za compensation order.
[ ] SG-COMP-014: Dodati test za failed compensation.
[ ] SG-COMP-015: Dodati test za duplicate compensation command.
```

Acceptance criteria:

```txt
[ ] Compensation nije "rollback baze".
[ ] Compensation je business action.
[ ] Compensation order je deterministički.
[ ] Failed compensation ne nestaje.
```

---

## ResumeSaga

Purpose: recovery posle pada procesa, timeout-a ili delimičnog neuspeha.

ToDo:

```txt
[ ] SG-RESUME-001: Napraviti SagaRecoveryPlan.
[ ] SG-RESUME-002: Napraviti SagaRecoveryFailure.
[ ] SG-RESUME-003: Napraviti ResumeSaga.
[ ] SG-RESUME-004: Napraviti FindRecoverableSaga.
[ ] SG-RESUME-005: Napraviti RebuildSagaState.
[ ] SG-RESUME-006: Napraviti ContinueSagaAfterFailure.
[ ] SG-RESUME-007: Napraviti MarkSagaAsUnrecoverable.
[ ] SG-RESUME-008: Definisati recoverable states.
[ ] SG-RESUME-009: Definisati unrecoverable states.
[ ] SG-RESUME-010: Definisati timeout behavior.
[ ] SG-RESUME-011: Dodati test za resume after process crash.
[ ] SG-RESUME-012: Dodati test za unrecoverable saga.
```

Acceptance criteria:

```txt
[ ] Saga može da se obnovi iz stanja/eventa.
[ ] Recovery path je eksplicitan.
[ ] Unrecoverable nije generic exception, nego stanje.
```

---

## ProtectSagaIdempotency

Purpose: sprečava duplo izvršenje komandi, stepova i compensation-a.

ToDo:

```txt
[ ] SG-IDEM-001: Napraviti SagaCommandKey.
[ ] SG-IDEM-002: Napraviti SagaCommandResult.
[ ] SG-IDEM-003: Napraviti DuplicateSagaCommand.
[ ] SG-IDEM-004: Napraviti ProtectSagaIdempotency.
[ ] SG-IDEM-005: Napraviti DetectDuplicateSagaCommand.
[ ] SG-IDEM-006: Napraviti RecordSagaCommandKey.
[ ] SG-IDEM-007: Napraviti ReadPreviousSagaCommandResult.
[ ] SG-IDEM-008: Definisati idempotency key format.
[ ] SG-IDEM-009: Definisati command result replay.
[ ] SG-IDEM-010: Dodati test da duplicate command vraća prethodni rezultat.
[ ] SG-IDEM-011: Dodati test da command key kolizija failuje bez side effect-a.
```

Acceptance criteria:

```txt
[ ] Retry ne pravi duple side effect-e.
[ ] Duplicate command je business-normal case.
[ ] Previous result može da se vrati bez re-execution.
```

---

## StoreSagaState

Purpose: čuva saga state i event history.

ToDo:

```txt
[ ] SG-STORE-001: Napraviti SagaState.
[ ] SG-STORE-002: Napraviti SagaEvent.
[ ] SG-STORE-003: Napraviti SagaStateStoreFailure.
[ ] SG-STORE-004: Napraviti StoreSagaState.
[ ] SG-STORE-005: Napraviti SaveSagaState.
[ ] SG-STORE-006: Napraviti ReadSagaState.
[ ] SG-STORE-007: Napraviti AppendSagaEvent.
[ ] SG-STORE-008: Napraviti ReadSagaEvents.
[ ] SG-STORE-009: Definisati optimistic concurrency token.
[ ] SG-STORE-010: Definisati state transition rules.
[ ] SG-STORE-011: Definisati event ordering.
[ ] SG-STORE-012: Dodati test za concurrent update conflict.
[ ] SG-STORE-013: Dodati test za event append.
[ ] SG-STORE-014: Dodati test za rebuild from events ako podržiš event reconstruction.
```

Acceptance criteria:

```txt
[ ] Saga state je source of recovery.
[ ] Saga event je source of diagnostics.
[ ] Concurrency conflict je eksplicitan.
[ ] Store ne zna business step logic.
```

---

## InspectSaga

Purpose: timeline, tracing, report, debug.

ToDo:

```txt
[ ] SG-INSP-001: Napraviti SagaRuntimeEvent.
[ ] SG-INSP-002: Napraviti SagaTimeline.
[ ] SG-INSP-003: Napraviti SagaReport.
[ ] SG-INSP-004: Napraviti InspectSaga.
[ ] SG-INSP-005: Napraviti RecordSagaEvent.
[ ] SG-INSP-006: Napraviti BuildSagaTimeline.
[ ] SG-INSP-007: Napraviti BuildSagaReport.
[ ] SG-INSP-008: Napraviti TraceSagaFailure.
[ ] SG-INSP-009: Definisati correlation id.
[ ] SG-INSP-010: Definisati causation id.
[ ] SG-INSP-011: Definisati safe payload logging.
[ ] SG-INSP-012: Dodati test da sensitive payload ne curi u report.
```

Acceptance criteria:

```txt
[ ] Možeš da objasniš šta se desilo u sagi.
[ ] Timeline je rekonstruisan.
[ ] Failure ima korelaciju.
[ ] Sensitive data ne curi u diagnostics.
```

---

# 8. Dokumentacioni ToDo za oba sistema

Svaki `how-this-works.md` mora imati realan trigger, realan upstream handoff, Mermaid diagram, realne file/function nazive, šta se piše/menja, šta korisnik vidi i gde se debugguje prvo. To je direktno iz how-to-document i review gates.

ToDo:

```txt
[ ] DOC-001: Kreirati docs/Foundation/DataLayer/how-this-works.md.
[ ] DOC-002: Kreirati docs/Foundation/ApplicationWorkflow/how-this-works.md.
[ ] DOC-003: Kreirati docs/Foundation/ApplicationWorkflow/Saga/how-this-works.md.
[ ] DOC-004: Za svaki source folder kreirati matching docs folder.
[ ] DOC-005: Za svaki source folder dodati how-this-works.md.
[ ] DOC-006: Za svaki PHP file dodati matching .md file.
[ ] DOC-007: Svaki how-this-works.md mora imati valid frontmatter.
[ ] DOC-008: Svaki how-this-works.md mora imati Mermaid sequenceDiagram ili flowchart.
[ ] DOC-009: Svaki Mermaid diagram mora koristiti realne participant nazive.
[ ] DOC-010: Zabraniti placeholder-e tipa Upstream, Main Handler, Data Handler.
[ ] DOC-011: Zabraniti fraze "handles", "works with", "supports" bez konkretizacije.
[ ] DOC-012: Dodati "Debug first" sekciju u svaki how-this-works.md.
[ ] DOC-013: Dodati "Dictionary" sekciju za DataLayer terms.
[ ] DOC-014: Dodati "Dictionary" sekciju za Saga terms.
[ ] DOC-015: Dodati "What this folder is" za svaki folder.
[ ] DOC-016: Dodati "Exact upstream handoffs" za svaki internal-only folder.
[ ] DOC-017: Dodati "Real commands that reach this folder" za command-facing foldere.
[ ] DOC-018: Dodati "What gets written or changed" u svaku relevantnu stranicu.
[ ] DOC-019: Dodati "Failure path" u svaku relevantnu stranicu.
[ ] DOC-020: Pokrenuti documentation review gate.
```

Acceptance criteria:

```txt
[ ] Novi reader može da objasni jedan realan path bez otvaranja koda.
[ ] Dokumentacija ne prepričava syntax.
[ ] Dokumentacija objašnjava intent, tradeoff i posledice.
[ ] Svaki source file ima docs mirror.
```

---

# 9. Test plan

## DataLayer tests

```txt
[ ] TEST-DL-001: DataLayer config validation.
[ ] TEST-DL-002: Database runtime registration.
[ ] TEST-DL-003: Persistent read path.
[ ] TEST-DL-004: Persistent write path.
[ ] TEST-DL-005: Raw query named params.
[ ] TEST-DL-006: Transaction commit.
[ ] TEST-DL-007: Transaction rollback.
[ ] TEST-DL-008: Deadlock retry allowed only when safe.
[ ] TEST-DL-009: Non-idempotent operation is not retried.
[ ] TEST-DL-010: Schema destructive change detection.
[ ] TEST-DL-011: Schema drift detection.
[ ] TEST-DL-012: Query validation invalid field.
[ ] TEST-DL-013: Query explain path.
[ ] TEST-DL-014: N+1 detection scenario.
[ ] TEST-DL-015: System invalidation policy required.
[ ] TEST-DL-016: Read replica lag warning.
[ ] TEST-DL-017: Shard routing determinism.
[ ] TEST-DL-018: Event duplicate deduplication.
[ ] TEST-DL-019: Outbox message recorded transactionally.
[ ] TEST-DL-020: Tenant boundary enforcement.
[ ] TEST-DL-021: Sensitive data masking.
[ ] TEST-DL-022: Audit trail record.
[ ] TEST-DL-023: Backup policy verification.
[ ] TEST-DL-024: Health report generation.
[ ] TEST-DL-025: Sensitive values do not leak into telemetry.
```

## Saga tests

```txt
[ ] TEST-SG-001: Valid saga definition.
[ ] TEST-SG-002: Invalid saga definition fails before runtime.
[ ] TEST-SG-003: Start saga creates state.
[ ] TEST-SG-004: Start saga records event.
[ ] TEST-SG-005: Duplicate start command is idempotent.
[ ] TEST-SG-006: Run successful step.
[ ] TEST-SG-007: Failed step records failure.
[ ] TEST-SG-008: Retryable step failure schedules retry.
[ ] TEST-SG-009: Non-retryable step failure triggers compensation.
[ ] TEST-SG-010: Compensation order is reverse of completed side-effect steps.
[ ] TEST-SG-011: Failed compensation marks saga recoverable or unrecoverable.
[ ] TEST-SG-012: Resume saga after process crash.
[ ] TEST-SG-013: Duplicate saga command returns previous result.
[ ] TEST-SG-014: Concurrent saga state update fails safely.
[ ] TEST-SG-015: Completed saga cannot run another step.
[ ] TEST-SG-016: Saga completed event goes through outbox.
[ ] TEST-SG-017: Saga timeline can be built.
[ ] TEST-SG-018: Sensitive payload does not leak into saga report.
```

---

# 10. Review gates

Po code-review dokumentu, enterprise review mora da završi jasnom odlukom: Keep and Improve, Redesign ili Rewrite Candidate. Takođe mora imati as-built flow, primary axis, responsibility/boundary map i invariants.

Za ovaj rad review gate treba da bude:

```txt
docs/Foundation/DataLayer/Code-Review-And-ToDo/review.md
docs/Foundation/ApplicationWorkflow/Saga/Code-Review-And-ToDo/review.md
```

## DataLayer review checklist

```txt
[ ] Primary axis: Data access and data correctness boundary.
[ ] Secondary axis, ako postoji: Data operations and governance.
[ ] As-built flow: Application -> DataLayer -> Database Runtime -> Result.
[ ] Boundary map: DataLayer vs Database vs Saga vs Platform.
[ ] Invariants definisani.
[ ] Nema generic bucket foldera.
[ ] Nema hidden Database access mimo UseDatabaseRuntime.
[ ] Nema Saga logic u DataLayer.
[ ] Nema retry policy bez idempotency pravila.
[ ] Nema cache bez invalidation pravila.
[ ] Nema tenant scoping discipline-only.
[ ] Nema telemetry sa sensitive data.
```

## Saga review checklist

```txt
[ ] Primary axis: Long-running workflow state transition.
[ ] Secondary axis, ako postoji: Compensation and recovery.
[ ] As-built flow: StartSaga -> RunSagaStep -> CompleteSaga or CompensateSaga.
[ ] Boundary map: Saga vs DataLayer vs MessageBus vs Business flow.
[ ] Invariants definisani.
[ ] Nema database internals u Saga logic.
[ ] Nema hidden orchestration.
[ ] Nema step bez explicit state transition.
[ ] Nema compensation bez recorded result.
[ ] Nema retry bez idempotency.
[ ] Nema sensitive payload u diagnostics.
```

---

# 11. System invariants

## DataLayer invariants

```txt
[ ] DL-INV-001: DataLayer ne sme da direktno implementira database engine.
[ ] DL-INV-002: DataLayer ne sme da sakrije SQL realnost.
[ ] DL-INV-003: Svaka transaction boundary mora biti eksplicitna.
[ ] DL-INV-004: Svaki retry mora biti safe ili odbijen.
[ ] DL-INV-005: Svaki cache mora imati invalidation policy.
[ ] DL-INV-006: Svaki schema change mora imati risk classification.
[ ] DL-INV-007: Svaki tenant boundary mora biti sistemski enforced.
[ ] DL-INV-008: Svaki published change mora biti deduplicatable.
[ ] DL-INV-009: Sensitive data ne sme u telemetry/event/log payload.
[ ] DL-INV-010: Foundation/Database je dependency, ne duplicated implementation.
```

## Saga invariants

```txt
[ ] SG-INV-001: Saga instance mora imati stable correlation id.
[ ] SG-INV-002: Saga state transition mora biti recorded.
[ ] SG-INV-003: Completed saga je terminal.
[ ] SG-INV-004: Failed step mora imati failure event.
[ ] SG-INV-005: Compensation mora biti idempotentna.
[ ] SG-INV-006: Compensation order mora biti deterministic.
[ ] SG-INV-007: Retry ne sme duplirati side effect.
[ ] SG-INV-008: Duplicate command mora vratiti prethodni rezultat ili failovati bez side effect-a.
[ ] SG-INV-009: Saga recovery mora biti moguća iz stored state/event history.
[ ] SG-INV-010: Saga ne sme da zavisi od Database internals.
```

---

# 12. Redosled implementacije

Ne bih radio sve odjednom. Ovo je pametan redosled:

```txt
1. DataLayer docs + boundary map
2. DataLayer skeleton
3. DataLayer ConfigureDataLayer
4. DataLayer AccessPersistentData
5. DataLayer CommitDataChanges
6. DataLayer PropagateDataChanges
7. DataLayer ProtectStoredData
8. DataLayer InspectDataLayer
9. Saga docs + boundary map
10. Saga skeleton
11. Saga DefineSaga
12. Saga StoreSagaState
13. Saga StartSaga
14. Saga RunSagaStep
15. Saga ProtectSagaIdempotency
16. Saga CompensateSaga
17. Saga ResumeSaga
18. Saga InspectSaga
19. Cross-component integration tests
20. Enterprise review gate
```

Zašto ovako?

Prvo zaključavaš granice. Onda praviš minimalni runtime. Onda uvodiš state i transaction. Tek onda ideš na recovery, compensation i observability. Ako kreneš od “kompletnog Saga engine-a”, vrlo lako ćeš napraviti framework-shaped warehouse. To tvoji how-to dokumenti baš pokušavaju da spreče. ⚠️

---

# 13. Minimalni prvi milestone

Ako želiš prvi realan, čist milestone, neka bude ovo:

```txt
MILESTONE 1: DataLayer and Saga Architecture Skeleton

[ ] Kreiran Foundation/DataLayer tree.
[ ] Kreiran Foundation/ApplicationWorkflow/Saga tree.
[ ] Kreiran docs mirror za oba.
[ ] Napisani root how-this-works.md fajlovi.
[ ] Napisani Code-Review-And-ToDo/todo.md fajlovi.
[ ] DataLayer koristi Foundation/Database samo kroz UseDatabaseRuntime.
[ ] Saga koristi DataLayer samo kroz transaction/outbox/state boundaries.
[ ] Nema business saga logic u DataLayer.
[ ] Nema database internals u Saga.
[ ] Svi root owner file-ovi imaju minimalne public metode.
[ ] Testovi pokrivaju config validation, transaction boundary, saga start, saga step, idempotency.
```

To je zdrav prvi korak. Posle toga širiš capability po capability.

---

# 14. Najvažnija pravila za Codex

Ovo bih bukvalno stavio u prompt:

```txt
Strictly follow all how-to-*.md governance documents.

Do not treat DataLayer as a replacement for Foundation/Database.
Foundation/Database remains the concrete database runtime.
Foundation/DataLayer is the data architecture and policy umbrella.

Do not place Saga inside DataLayer.
Saga belongs to Foundation/ApplicationWorkflow/Saga.

Use this naming law everywhere:
folder says flow or capability;
file says responsibility;
function says exact action.

Do not create Services, Helpers, Utils, Common, Shared, Managers, Core, Base, or generic bucket folders.

Every source folder must have a mirrored docs folder under docs/.
Every ownership folder must have a how-this-works.md in the docs mirror.
Every PHP file must have a matching Markdown documentation file.

Every how-this-works.md must use:
- valid frontmatter
- real command or trigger
- exact upstream handoffs
- real file/function names
- Mermaid sequenceDiagram or flowchart
- what gets written, changed, rendered, or executed
- failure path
- debug-first section
- dictionary section where terms can be confused

Implement in small safe increments.
Do not move existing Foundation/Database files unless explicitly required.
Prefer adapter/handoff boundaries over large rewrites.
Add tests for behavior, contracts, invariants, security boundaries, idempotency, and failure modes.
```

Finalna presuda: ovo je dobar smer. Samo ne pravi od `DataLayer` “novi Database sa lepšim imenom”. DataLayer je standard i orchestration boundary za data concerns. Saga je posebna workflow mašina. Ako ih razdvojiš ovako, dobijaš sistem koji je čitljiv, proširiv i ne laže o vlasništvu.
