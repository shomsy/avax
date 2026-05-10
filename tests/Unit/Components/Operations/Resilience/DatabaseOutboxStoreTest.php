<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\Outbox\DatabaseOutboxStore;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DatabaseOutboxStoreTest extends TestCase
{
    private PDO $pdo;
    private DatabaseOutboxStore $store;

    protected function setUp() : void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec(
            "CREATE TABLE avax_outbox (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                event_type VARCHAR(255) NOT NULL,
                payload TEXT NOT NULL,
                processed INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                processed_at DATETIME DEFAULT NULL
            )",
        );

        $this->store = new DatabaseOutboxStore($this->pdo, 'avax_outbox');
    }

    #[Test]
    public function enqueue_adds_message() : void
    {
        $this->store->enqueue('user.created', ['user_id' => 1]);

        self::assertSame(1, $this->store->pendingCount());
    }

    #[Test]
    public function dequeue_returns_pending_messages() : void
    {
        $this->store->enqueue('user.created', ['user_id' => 1]);
        $this->store->enqueue('order.placed', ['order_id' => 42]);

        $messages = $this->store->dequeue(limit: 10);

        self::assertCount(2, $messages);
        self::assertSame('user.created', $messages[0]['event']);
    }

    #[Test]
    public function dequeue_respects_limit() : void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->store->enqueue('event.test', ['i' => $i]);
        }

        $messages = $this->store->dequeue(limit: 2);

        self::assertCount(2, $messages);
    }

    #[Test]
    public function markProcessed_removes_from_pending() : void
    {
        $this->store->enqueue('user.created', ['user_id' => 1]);
        $messages = $this->store->dequeue(limit: 1);

        $this->store->markProcessed($messages[0]['id']);

        self::assertSame(0, $this->store->pendingCount());
    }

    #[Test]
    public function pendingCount_excludes_processed() : void
    {
        $this->store->enqueue('event.a', []);
        $this->store->enqueue('event.b', []);
        $this->store->enqueue('event.c', []);

        $messages = $this->store->dequeue(limit: 2);
        $this->store->markProcessed($messages[0]['id']);
        $this->store->markProcessed($messages[1]['id']);

        self::assertSame(1, $this->store->pendingCount());
    }

    #[Test]
    public function dequeue_returns_empty_when_all_processed() : void
    {
        $this->store->enqueue('event.test', []);
        $messages = $this->store->dequeue(limit: 1);
        $this->store->markProcessed($messages[0]['id']);

        self::assertSame([], $this->store->dequeue(limit: 10));
    }
}
