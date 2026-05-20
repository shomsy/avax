<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Migrations;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\SeedDatabase\Seeder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SeederSecurityTest extends TestCase
{
    private QueryBuilder $builder;

    public function test_rejects_non_existent_seeder_class_in_call(): void
    {
        $seeder = $this->createParentSeeder();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');

        $seeder->call('NonExistentSeederClass');
    }

    public function test_rejects_class_not_extending_seeder_in_call(): void
    {
        $seeder = $this->createParentSeeder();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not extend Seeder');

        $seeder->call(NotASeeder::class);
    }

    public function test_accepts_valid_seeder_class_in_call(): void
    {
        $seeder = $this->createParentSeeder();

        $seeder->call(ValidTestSeeder::class);

        $this->expectNotToPerformAssertions();
    }

    private function createParentSeeder(): Seeder
    {
        $seeder = new class extends Seeder {
            public function run(): void {}
        };
        $seeder->withBuilder($this->builder);

        return $seeder;
    }

    protected function setUp(): void
    {
        $this->builder = $this->createMock(QueryBuilder::class);
    }
}

final class NotASeeder
{
    public function run(): void {}
}

final class ValidTestSeeder extends Seeder
{
    public function run(): void {}
}
