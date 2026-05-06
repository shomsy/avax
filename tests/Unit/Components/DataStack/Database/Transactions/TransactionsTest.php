<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Transactions;

use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions;
use PHPUnit\Framework\TestCase;

final class TransactionsTest extends TestCase
{
    public function test_transactions_class_exists(): void
    {
        $this->assertTrue(class_exists(Transactions::class));
    }

    public function test_transactions_has_begin_method(): void
    {
        $this->assertTrue(method_exists(Transactions::class, 'begin'));
    }

    public function test_transactions_has_commit_method(): void
    {
        $this->assertTrue(method_exists(Transactions::class, 'commit'));
    }

    public function test_transactions_has_rollback_method(): void
    {
        $this->assertTrue(method_exists(Transactions::class, 'rollback'));
    }

    public function test_transactions_has_transaction_method(): void
    {
        $this->assertTrue(method_exists(Transactions::class, 'transaction'));
    }

    public function test_transactions_has_reset_method(): void
    {
        $this->assertTrue(method_exists(Transactions::class, 'reset'));
    }

    public function test_transactions_has_depth_method(): void
    {
        $this->assertTrue(method_exists(Transactions::class, 'depth'));
    }

    public function test_transactions_has_is_active_method(): void
    {
        $this->assertTrue(method_exists(Transactions::class, 'isActive'));
    }
}
