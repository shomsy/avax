<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Database\Unit;

use Avax\Database\System\Capabilities\ORM\Attributes\Column;
use Avax\Database\System\Capabilities\ORM\Attributes\Entity;
use Avax\Database\System\Capabilities\ORM\Attributes\GeneratedValue;
use Avax\Database\System\Capabilities\ORM\Attributes\Id;
use Avax\Database\System\Capabilities\ORM\Attributes\Table;
use Avax\Database\System\Capabilities\ORM\Repositories\EntityRepository;
use Avax\Database\System\Capabilities\Query\Exceptions\QueryException;
use Avax\Database\System\Capabilities\Transactions\Exceptions\TransactionException;
use Avax\Tests\TestCase;
use Exception;
use PDO;
use Throwable;

/**
 * Critical path test: Transactions, ORM lifecycle, and query exception redaction.
 */
final class CriticalPathTest extends TestCase
{
    /**
     * Test: Transaction rollback on inner failure.
     *
     * @throws Throwable
     */
    public function test_transaction_rollback_on_failure() : void
    {
        try {
            $this->database->transactions()->run(callback: function () {
                $this->database->table(table: 'users')->insert(values: [
                                                                           'name'  => 'Alice',
                                                                           'email' => 'alice@test.com',
                                                                       ]);

                throw new Exception(message: 'Simulated failure');
            });
        } catch (TransactionException $e) {
            $this->assertStringContainsString(
                needle  : 'Simulated failure',
                haystack: $e->getMessage()
            );
            $this->assertInstanceOf(expected: Exception::class, actual: $e->getPrevious());
            $this->assertSame(expected: 'Simulated failure', actual: $e->getPrevious()?->getMessage());
        }

        $count = $this->database->table(table: 'users')->count();
        $this->assertSame(expected: 0, actual: $count, message: 'Transaction should have rolled back');
    }

    /**
     * Test: EntityManager persists and rehydrates one mapped entity.
     *
     * @throws Throwable
     */
    public function test_entity_manager_persists_and_rehydrates_entity() : void
    {
        $entityManager = $this->database->entityManager();
        $user          = new TestUserEntity(name: 'Bob', email: 'bob@test.com');

        $entityManager->persist(entity: $user);
        $entityManager->flush();

        $this->assertNotNull(actual: $user->id);

        $loaded = $entityManager->find(entityClass: TestUserEntity::class, id: $user->id);
        $this->assertInstanceOf(expected: TestUserEntity::class, actual: $loaded);
        $this->assertSame(expected: $user, actual: $loaded);
        $this->assertSame(expected: 'Bob', actual: $loaded->name);
    }

    /**
     * Test: Entity metadata can resolve a custom repository class.
     *
     * @throws Throwable
     */
    public function test_entity_manager_resolves_custom_repository() : void
    {
        $entityManager = $this->database->entityManager();
        $repository    = $entityManager->repository(entityClass: TestUserEntity::class);

        $this->assertInstanceOf(expected: TestUserRepository::class, actual: $repository);

        $user = new TestUserEntity(name: 'Carol', email: 'carol@test.com');
        $entityManager->persist(entity: $user);
        $entityManager->flush();

        $loaded = $repository->findByEmail(email: 'carol@test.com');

        $this->assertInstanceOf(expected: TestUserEntity::class, actual: $loaded);
        $this->assertSame(expected: 'Carol', actual: $loaded->name);
        $this->assertSame(expected: $user, actual: $loaded);
    }

    /**
     * Test: QueryException never exposes raw bindings by default.
     *
     * @throws Throwable
     */
    public function test_query_exception_redacts_bindings_by_default() : void
    {
        try {
            $this->database->table(table: 'nonexistent')->insert(values: ['secret' => 'password123']);
        } catch (QueryException $e) {
            $bindings = $e->getBindings();
            $this->assertSame(expected: ['[REDACTED]'], actual: $bindings);

            $rawBindings = $e->getBindings(redacted: false);
            $this->assertSame(expected: ['password123'], actual: $rawBindings);

            return;
        }

        $this->fail(message: 'Expected QueryException was not thrown.');
    }

    protected function setUp() : void
    {
        parent::setUp();

        $pdo = $this->database->connections()->pdo();
        $pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
        // noinspection SqlNoDataSourceInspection
        $pdo->exec(statement: 'CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)');
    }
}

#[Entity(repositoryClass: TestUserRepository::class)]
#[Table(name: 'users')]
final class TestUserEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column(name: 'id', type: 'integer')]
    public int|null $id = null;

    #[Column(name: 'name', type: 'string')]
    public string $name;

    #[Column(name: 'email', type: 'string')]
    public string $email;

    public function __construct(string $name, string $email)
    {
        $this->name  = $name;
        $this->email = $email;
    }
}

final class TestUserRepository extends EntityRepository
{
    public function findByEmail(string $email) : TestUserEntity|null
    {
        $entity = $this->findOneBy(criteria: ['email' => $email]);

        return $entity instanceof TestUserEntity ? $entity : null;
    }
}
