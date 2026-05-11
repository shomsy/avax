<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Email;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\IntegerType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\StringType;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\CacheDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShapeCompiler;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\ReadClassDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferFailure;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use Avax\Components\DataStack\DataTransfer\System\Flows\CreateDataObject\CreateDataObject;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests: CreateDataObject with DataShapeCompiler produces identical results.
 */
final class CreateDataObjectWithDataShapeTest extends TestCase
{
    private DataTransferConfig $config;
    private DataShapeCompiler $compiler;
    private CreateDataObject $compilerFlow;
    private CreateDataObject $baseFlow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = DataTransferConfig::default();
        $this->compiler = new DataShapeCompiler(
            inspectDataShape: new InspectDataShape(dataTransferConfig: $this->config),
            readClassDataShape: new ReadClassDataShape(),
        );
        $this->compilerFlow = new CreateDataObject(
            dataTransferConfig: $this->config,
            dataShapeCompiler: $this->compiler,
        );
        $this->baseFlow = new CreateDataObject(dataTransferConfig: $this->config);
        CacheDataShape::reset();
        DataShapeCompiler::reset();
    }

    protected function tearDown(): void
    {
        CacheDataShape::reset();
        DataShapeCompiler::reset();
        parent::tearDown();
    }

    #[Test]
    public function creates_object_with_constructor_promoted_properties(): void
    {
        $input = ['name' => 'AvaX', 'version' => 4];
        $base = $this->baseFlow->create(DSConstructorPromotedDto::class, $input);
        $compiled = $this->compilerFlow->create(DSConstructorPromotedDto::class, $input);

        $this->assertSame($base->name, $compiled->name);
        $this->assertSame($base->version, $compiled->version);
    }

    #[Test]
    public function creates_object_with_no_constructor_properties(): void
    {
        $input = ['full_name' => 'AvaX', 'count' => 10];
        $base = $this->baseFlow->create(DSCDONoConstructorDto::class, $input);
        $compiled = $this->compilerFlow->create(DSCDONoConstructorDto::class, $input);

        $this->assertSame($base->name, $compiled->name);
        $this->assertSame($base->count, $compiled->count);
    }

    #[Test]
    public function throws_when_required_field_is_missing(): void
    {
        $this->expectException(DataTransferFailure::class);
        $this->compilerFlow->create(DSConstructorPromotedDto::class, ['version' => 1]);
    }

    #[Test]
    public function optional_field_is_skipped_when_missing(): void
    {
        $base = $this->baseFlow->create(DSOptionalDto::class, ['name' => 'test']);
        $compiled = $this->compilerFlow->create(DSOptionalDto::class, ['name' => 'test']);

        $this->assertSame($base->name, $compiled->name);
    }

    #[Test]
    public function default_value_is_applied_when_field_missing(): void
    {
        $base = $this->baseFlow->create(DSCDODefaultDto::class, ['name' => 'test']);
        $compiled = $this->compilerFlow->create(DSCDODefaultDto::class, ['name' => 'test']);

        $this->assertSame($base->role, $compiled->role);
    }

    #[Test]
    public function validates_type_mismatch_for_string(): void
    {
        $this->expectException(DataTransferFailure::class);
        $this->compilerFlow->create(DSCreateDataObjectTypedDto::class, ['name' => 12345, 'count' => 1]);
    }

    #[Test]
    public function validates_type_mismatch_for_int(): void
    {
        $this->expectException(DataTransferFailure::class);
        $this->compilerFlow->create(DSCreateDataObjectTypedDto::class, ['name' => 'ok', 'count' => 'not-int']);
    }

    #[Test]
    public function validates_email_attribute(): void
    {
        $this->expectException(DataTransferFailure::class);
        $this->compilerFlow->create(DSCDOEmailDto::class, ['email' => 'not-an-email']);
    }

    #[Test]
    public function accepts_valid_email(): void
    {
        $base = $this->baseFlow->create(DSCDOEmailDto::class, ['email' => 'user@example.com']);
        $compiled = $this->compilerFlow->create(DSCDOEmailDto::class, ['email' => 'user@example.com']);

        $this->assertSame($base->email, $compiled->email);
    }

    #[Test]
    public function casts_nested_dto_from_array(): void
    {
        $base = $this->baseFlow->create(DSCDONestedDto::class, [
            'child' => ['name' => 'child-name', 'version' => 2],
        ]);
        $compiled = $this->compilerFlow->create(DSCDONestedDto::class, [
            'child' => ['name' => 'child-name', 'version' => 2],
        ]);

        $this->assertSame($base->child->name, $compiled->child->name);
    }

    #[Test]
    public function casts_list_of_nested_dtos(): void
    {
        $input = [
            'items' => [
                ['name' => 'first', 'version' => 1],
                ['name' => 'second', 'version' => 2],
            ],
        ];
        $base = $this->baseFlow->create(DSCDOListDto::class, $input);
        $compiled = $this->compilerFlow->create(DSCDOListDto::class, $input);

        $this->assertCount(2, $compiled->items);
        $this->assertSame($base->items[0]->name, $compiled->items[0]->name);
    }

    #[Test]
    public function applies_custom_caster(): void
    {
        $base = $this->baseFlow->create(DSCDOCastDto::class, ['value' => 'HELLO WORLD']);
        $compiled = $this->compilerFlow->create(DSCDOCastDto::class, ['value' => 'HELLO WORLD']);

        $this->assertSame($base->value, $compiled->value);
    }

    #[Test]
    public function resolves_map_from_attribute(): void
    {
        $base = $this->baseFlow->create(DSCDOMappedDto::class, ['full_name' => 'AvaX']);
        $compiled = $this->compilerFlow->create(DSCDOMappedDto::class, ['full_name' => 'AvaX']);

        $this->assertSame($base->name, $compiled->name);
    }

    #[Test]
    public function hydrate_into_with_compiler_produces_same_violations(): void
    {
        $dto1 = new DSCDONoConstructorDto();
        $dto2 = new DSCDONoConstructorDto();

        $violations1 = $this->baseFlow->hydrateInto($dto1, []);
        $violations2 = $this->compilerFlow->hydrateInto($dto2, []);

        $this->assertCount(count($violations1), $violations2);
    }

    #[Test]
    public function hydrate_into_hydrates_existing_object(): void
    {
        $dto1 = new DSCDONoConstructorDto();
        $dto1->name = 'original';
        $dto1->count = 0;

        $dto2 = new DSCDONoConstructorDto();
        $dto2->name = 'original';
        $dto2->count = 0;

        $this->baseFlow->hydrateInto($dto1, ['full_name' => 'updated', 'count' => 42]);
        $this->compilerFlow->hydrateInto($dto2, ['full_name' => 'updated', 'count' => 42]);

        $this->assertSame($dto1->name, $dto2->name);
        $this->assertSame($dto1->count, $dto2->count);
    }

    #[Test]
    public function extends_data_object_and_creates_successfully(): void
    {
        $base = $this->baseFlow->create(DSDataObjectChild::class, ['name' => 'child', 'count' => 5]);
        $compiled = $this->compilerFlow->create(DSDataObjectChild::class, ['name' => 'child', 'count' => 5]);

        $this->assertInstanceOf(DataObject::class, $compiled);
        $this->assertSame($base->name, $compiled->name);
        $this->assertSame($base->count, $compiled->count);
    }
}

// Test DTOs (mirrors from CreateDataObjectTest)
final class DSConstructorPromotedDto
{
    public function __construct(
        #[Required]
        #[StringType]
        public string $name,
        #[Optional]
        #[IntegerType]
        public int|null $version = null,
    ) {}
}

final class DSCDONoConstructorDto extends DataObject
{
    #[Required]
    #[StringType]
    #[MapFrom('full_name')]
    public string $name;

    #[Required]
    #[IntegerType]
    public int $count;
}

final class DSCreateDataObjectTypedDto
{
    public function __construct(
        #[Required]
        public string $name,
        #[Required]
        public int $count,
    ) {}
}

final class DSOptionalDto
{
    public function __construct(
        #[Required]
        public string $name,
        #[Optional]
        public string|null $tag = null,
    ) {}
}

final class DSCDODefaultDto
{
    public function __construct(
        #[Required]
        public string $name,
        #[DefaultValue('fallback')]
        public string $role = 'fallback',
    ) {}
}

final class DSCDOEmailDto
{
    public function __construct(
        #[Required]
        #[Email]
        public string $email,
    ) {}
}

enum DSTestStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

final class DSCDOEnumDto
{
    public function __construct(
        #[Required]
        public DSTestStatus $status,
    ) {}
}

final class DSCDONestedDto
{
    public function __construct(
        #[Required]
        public DSConstructorPromotedDto $child,
    ) {}
}

final class DSCDOListDto
{
    /**
     * @param list<DSConstructorPromotedDto> $items
     */
    public function __construct(
        #[Required]
        #[ListOf(DSConstructorPromotedDto::class)]
        public array $items,
    ) {}
}

final class DSLowerCaster
{
    public function cast(mixed $value, string $field): mixed
    {
        return strtolower((string) $value);
    }
}

final class DSCDOCastDto
{
    public function __construct(
        #[Required]
        #[CastWith(DSLowerCaster::class)]
        public string $value,
    ) {}
}

final class DSCDOMappedDto
{
    public function __construct(
        #[Required]
        #[MapFrom('full_name')]
        public string $name,
    ) {}
}

final class DSDataObjectChild extends DataObject
{
    #[Required]
    public string $name;

    #[Required]
    public int $count;
}
