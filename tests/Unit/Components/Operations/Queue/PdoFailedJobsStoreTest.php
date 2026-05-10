<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\PdoFailedJobsStore;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PdoFailedJobsStoreTest extends TestCase
{
    private PDO $pdo;

    #[Test]
    public function it_accepts_valid_table_name() : void
    {
        $store = new PdoFailedJobsStore($this->pdo, 'valid_table_name');
        $store->record('default', ['id' => '1'], 'test', '2026-01-01');

        self::assertSame(1, $store->count());
    }

    #[Test]
    public function it_accepts_table_name_starting_with_underscore() : void
    {
        $store = new PdoFailedJobsStore($this->pdo, '_underscore_table');
        self::assertSame(0, $store->count());
    }

    #[Test]
    public function it_rejects_table_name_with_spaces() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');

        new PdoFailedJobsStore($this->pdo, 'bad table name');
    }

    #[Test]
    public function it_rejects_table_name_with_sql_injection() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');

        new PdoFailedJobsStore($this->pdo, "jobs; DROP TABLE users;--");
    }

    #[Test]
    public function it_rejects_table_name_starting_with_number() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');

        new PdoFailedJobsStore($this->pdo, '123_invalid');
    }

    #[Test]
    public function it_rejects_empty_table_name() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');

        new PdoFailedJobsStore($this->pdo, '');
    }

    #[Test]
    public function it_rejects_table_name_with_special_chars() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');

        new PdoFailedJobsStore($this->pdo, "table'OR'1'='1");
    }

    protected function setUp() : void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}
