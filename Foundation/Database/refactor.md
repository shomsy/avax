Da. Sada već ima dovoljno stvarnog materijala da se nacrta **finalan target tree**, a ne samo smer. Zadržao bih ono što
je već dobro postavljeno: `System/` kao pravi system root, `Database.php` i `DatabaseInterface.php` kao mali stabilni
public surface, `DatabaseBuilder.php` kao jedini composition root, i capability podelu na connections, query builder,
migrations, transactions i telemetry. To već postoji i već ide u dobrom smeru. Ono što fali je da svaka capability
dobije još pošteniji, glasniji local shape.

Ja bih išao na ovakav target:

```text
Database/
  System/
    how-this-works.md

    Database.php
    DatabaseInterface.php

    Capabilities/
      how-this-works.md

      Connections/
        how-this-works.md
        Connections.php

        ReadConnection/
          how-this-works.md
          ReadConnection.php
          ReadPdo.php
          ResolveDefaultConnection.php
          RememberConnection.php

        OpenConnection/
          how-this-works.md
          OpenConnection.php
          BuildPhysicalConnection.php
          PdoConnection.php

        RunWithConnection/
          how-this-works.md
          RunWithConnection.php

        Pools/
          how-this-works.md
          ConnectionPool.php
          BorrowedConnection.php
          PooledConnectionAuthority.php
          PoolState.php

          Contracts/
            how-this-works.md
            ConnectionPoolInterface.php

          DTO/
            how-this-works.md
            ConnectionPoolMetrics.php

        Contracts/
          how-this-works.md
          DatabaseConnection.php

        ValueObjects/
          how-this-works.md
          ConnectionConfig.php
          Dsn.php

        Exceptions/
          how-this-works.md
          ConnectionException.php
          ConnectionFailure.php
          PoolLimitReachedException.php

      Querying/
        how-this-works.md
        Querying.php

        CreateBuilder/
          how-this-works.md
          CreateBuilder.php

        Builder/
          how-this-works.md
          QueryBuilder.php
          JoinClause.php

          Concerns/
            HasAdvancedMutations.php
            HasAggregates.php
            HasConditions.php
            HasControlStructures.php
            HasGroups.php
            HasJoins.php
            HasOrders.php
            HasSchema.php
            HasSoftDeletes.php
            Macroable.php

        Execution/
          how-this-works.md
          QueryOrchestrator.php
          PDOExecutor.php

        Grammar/
          how-this-works.md
          GrammarInterface.php
          BaseGrammar.php
          MySQLGrammar.php

        State/
          how-this-works.md
          QueryState.php
          PaginationOptions.php

          AST/
            JoinNode.php
            NestedWhereNode.php
            OrderNode.php
            WhereNode.php

        Identity/
          how-this-works.md
          IdentityMap.php

        ValueObjects/
          how-this-works.md
          Expression.php
          ColumnIdentifier.php
          TableIdentifier.php
          QuotedIdentifier.php

        Enums/
          how-this-works.md
          Operator.php
          QueryBuilderEnum.php

        Exceptions/
          how-this-works.md
          InvalidCriteriaException.php
          QueryException.php

      Transactions/
        how-this-works.md
        Transactions.php

        OnConnection/
          how-this-works.md
          OnConnection.php

        RunTransaction/
          how-this-works.md
          RunTransaction.php
          Transaction.php
          TransactionScope.php

        Contracts/
          how-this-works.md
          TransactionManagerInterface.php

        Exceptions/
          how-this-works.md
          TransactionException.php

      Migrations/
        how-this-works.md
        Migrations.php

        Design/
          how-this-works.md
          BaseMigration.php

          Table/
            how-this-works.md
            Blueprint.php
            TableDefinition.php

          Column/
            DSL/
              how-this-works.md
              ColumnDefinition.php

            Render/
              how-this-works.md
              ColumnSQLRenderer.php

          TypeMapping/
            how-this-works.md
            SQLToPHPTypeMapper.php

        CreateMigration/
          how-this-works.md
          MigrationGenerator.php

          Stubs/
            blank.stub
            create.stub
            update.stub

        LoadMigrations/
          how-this-works.md
          MigrationLoader.php

        RunMigrations/
          how-this-works.md
          MigrationRunner.php
          MigrationRepository.php

        RollbackMigrations/
          how-this-works.md
          RollbackMigrations.php

        ReadMigrationStatus/
          how-this-works.md
          ReadMigrationStatus.php

        SeedDatabase/
          how-this-works.md
          Seeder.php

        ExportDatabase/
          how-this-works.md
          DatabaseExporter.php

        SchemaOperations/
          how-this-works.md
          CreateDatabase.php
          DropDatabase.php
          DropTable.php
          TruncateTable.php

        Exceptions/
          how-this-works.md
          MigrationException.php

      Telemetry/
        how-this-works.md
        Telemetry.php

        Events/
          how-this-works.md
          Event.php
          EventBus.php
          EventSubscriberInterface.php
          ConnectionOpened.php
          ConnectionFailed.php
          ConnectionAcquired.php
          QueryExecuted.php

          Subscribers/
            how-this-works.md
            DatabaseLoggerSubscriber.php

        Support/
          how-this-works.md
          ExecutionScope.php
          SequenceTracker.php

        Config/
          how-this-works.md
          Config.php

    Configuration/
      how-this-works.md
      DatabaseBuilder.php

    Foundation/
      how-this-works.md

      Exceptions/
        how-this-works.md
        DatabaseException.php
        DatabaseThrowable.php

  Integrations/
    how-this-works.md

    AvaxContainer/
      how-this-works.md
      DatabaseServiceProvider.php

    Console/
      how-this-works.md
      MakeMigrationCommand.php
      MigrateCommand.php
      MigrateRollbackCommand.php
      MigrateStatusCommand.php
      SeedCommand.php
      ExportCommand.php

  docs/
    System/
      ...
    Integrations/
      ...

  examples/
    querying/
      ...
    migrations/
      ...

  tests/
    System/
      ...
    Integrations/
      ...
```

Ovaj tree je namerno **capability-first na root-u**, jer i sadašnji sistem već jasno kaže da caller bira capability, a
`DatabaseBuilder::ready()` sastavlja capability ownere za connections, query builder, migrations, transactions i
telemetry. Znači, root priča sistema nije “jedan end-to-end flow”, nego “jedan database system sa više velikih
sposobnosti”. Zato ne bih uvodio lažni root `Flows/` samo da bi tree izgledao “više arhitektonski”. Flow treba da
postoji tamo gde je stvarno sekvenca, pa su zato `ReadConnection`, `OpenConnection`, `RunWithConnection`,
`CreateMigration`, `LoadMigrations`, `RunMigrations`, `RollbackMigrations`, `RunTransaction` i slični folderi lokalni
flow slice-ovi unutar capability-ja. To je direktno u skladu sa tvojim pravilima o fractal ownership-u.

Najvažnija rename odluka je da bih `QueryBuilder` capability preveo u **`Querying/`**, zato što ta zona već sada ne
poseduje samo builder, nego i grammar compilation, executor dispatch, raw expression/value objects, query exceptions i
identity-map ponašanje. Drugim rečima, to više nije “jedan builder”, nego cela query runtime priča. `QueryBuilder` neka
ostane unutra kao glavni fluent unit, ali capability treba da se zove šire i poštenije.

`Migrations` ostaje posebna velika capability, ali ne kao izolovan svet. `BaseMigration` već direktno zavisi od
`QueryBuilder` i grammar-a za stvarno izvršavanje schema SQL-a, što znači da migration capability prirodno stoji na
query runtime-u. Zato je ispravno da bude sibling capability, ali da joj unutrašnja priča vrišti kroz dizajn,
generisanje, učitavanje, izvršavanje, rollback, status, export i seed.

`Transactions` i `Telemetry` bih zadržao kao odvojene capability-je, ne bih ih gurao u foundation. Trenutna
dokumentacija za transactions već kaže da ta zona poseduje transaction boundaries, nested handling, savepoints i
deferred identity-map flush, a telemetry već poseduje event bus, correlation scope, sequence tracking i logger
subscribers. To su stvarne sposobnosti sistema, nisu neutralni atomi.

Ono što bih potpuno ubio iz target verzije su **stari lifecycle/module registry ostaci**. Tvoj novi
`Configuration/how-this-works.md` već eksplicitno kaže da `DatabaseBuilder` zamenjuje stari lifecycle i module registry
pattern, tako da `Manifest`, stari module boot/shutdown jezik i slični fosili ne treba da prežive u scream verziji. Isto
važi za generičke ownership nazive poput `ConnectionManager` kao finalnog centra sveta. To mogu biti prelazni adapteri
tokom refaktora, ali ne i konačni shape.

Jedna stvar koju bih tvrdoglavo sačuvao je public surface. `Database.php` i `DatabaseInterface.php` već nude jasan mali
ulaz sa `connections()`, `queryBuilder()/query()`, `migrations()/schema()`, `transactions()`, `telemetry()`, `builder()`
i `table()`. To je dobar paket-shaped entry i ne treba ga razbijati. Menjaš unutrašnju arhitekturu, ne rušiš lice
komponente bez potrebe.

