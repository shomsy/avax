<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\Required;
use Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection;
use Avax\Components\DataStack\Data\System\Capabilities\Coercion\DtoSystem\Attributes\Optional;
use Avax\Components\DataStack\Data\System\Capabilities\Coercion\DtoSystem\DataTransfer;
use Avax\Components\DataStack\Data\System\Capabilities\Coercion\DtoSystem\DataTransferFailure;
use PHPUnit\Framework\TestCase;

final class DataCapabilitiesTest extends TestCase
{
    public function test_collection_basic_operations() : void
    {
        $collection = Collection::make([1, 2, 3, 4, 5]);

        $doubled = $collection->map(fn ($n) => $n * 2);
        $this->assertSame([2, 4, 6, 8, 10], $doubled->all());

        $filtered = $collection->filter(fn ($n) => $n > 3);
        $this->assertSame([3 => 4, 4 => 5], $filtered->all()); // array_filter preserves keys

        $this->assertSame(1, $collection->first());
    }

    public function test_data_transfer_creates_dto() : void
    {
        $input = ['name' => 'AvaX', 'version' => 1];
        $dto   = DataTransfer::create(TestDataTransferDto::class, $input);

        $this->assertInstanceOf(TestDataTransferDto::class, $dto);
        $this->assertSame('AvaX', $dto->name);
        $this->assertSame(1, $dto->version);
    }

    public function test_data_transfer_fails_on_missing_required_field() : void
    {
        $input = ['version' => 1];

        $this->expectException(DataTransferFailure::class);
        $this->expectExceptionMessage('Data validation failed.');

        DataTransfer::create(TestDataTransferDto::class, $input);
    }

    public function test_data_transfer_supports_optional_fields() : void
    {
        $input = ['name' => 'AvaX'];
        $dto   = DataTransfer::create(TestDataTransferDto::class, $input);

        $this->assertSame('AvaX', $dto->name);
        $this->assertNull($dto->version);
    }
}

final class TestDataTransferDto
{
    public function __construct(
        #[Required]
        public string $name,
        #[Optional]
        public ?int   $version = null,
    ) {}
}
