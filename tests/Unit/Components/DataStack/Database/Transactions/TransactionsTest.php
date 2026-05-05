<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Transactions;

use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions;
use PHPUnit\Framework\TestCase;

final class TransactionsTest extends TestCase
{
    public function testTransactionsClassExists() : void
    {
        $this->assertTrue(class_exists(Transactions::class));
    }

    public function testTransactionsHasBeginMethod() : void
    {
        $this->assertTrue(method_exists(Transactions::class, 'begin'));
    }

    public function testTransactionsHasCommitMethod() : void
    {
        $this->assertTrue(method_exists(Transactions::class, 'commit'));
    }

    public function testTransactionsHasRollbackMethod() : void
    {
        $this->assertTrue(method_exists(Transactions::class, 'rollback'));
    }

    public function testTransactionsHasTransactionMethod() : void
    {
        $this->assertTrue(method_exists(Transactions::class, 'transaction'));
    }

    public function testTransactionsHasResetMethod() : void
    {
        $this->assertTrue(method_exists(Transactions::class, 'reset'));
    }

    public function testTransactionsHasDepthMethod() : void
    {
        $this->assertTrue(method_exists(Transactions::class, 'depth'));
    }

    public function testTransactionsHasIsActiveMethod() : void
    {
        $this->assertTrue(method_exists(Transactions::class, 'isActive'));
    }
}