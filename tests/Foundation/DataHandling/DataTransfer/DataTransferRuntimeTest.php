<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataHandling\DataTransfer;

use Avax\DataHandling\DataTransfer\Capabilities\Attributes\CastWith;
use Avax\DataHandling\DataTransfer\Capabilities\Attributes\Hidden;
use Avax\DataHandling\DataTransfer\Capabilities\Attributes\ListOf;
use Avax\DataHandling\DataTransfer\Capabilities\Attributes\MapFrom;
use Avax\DataHandling\DataTransfer\Capabilities\ValueConversion\ValueCasterInterface;
use Avax\DataHandling\DataTransfer\Capabilities\ValueConversion\ValueConversionContext;
use Avax\DataHandling\DataTransfer\DataTransfer;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataField;
use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\ObjectHandling\DTO\DTOValidationException;
use Avax\DataHandling\Validation\Attributes\Rules\EmailRule;
use Avax\DataHandling\Validation\Attributes\Rules\MinLengthRule;
use JsonException;
use PHPUnit\Framework\TestCase;

final class DataTransferRuntimeTest extends TestCase
{
    public function test_it_creates_native_constructor_data_object_when_input_matches_shape() : void
    {
        // Arrange
        $input = [
            'user_name' => '  Mira  ',
            'role'      => 'admin',
            'address'   => ['city' => 'Belgrade'],
            'addresses' => [
                ['city' => 'Novi Sad'],
            ],
            'secret'    => 'hashed',
        ];

        // Act
        $profile = DataTransfer::create(class: RuntimeProfileData::class, input: $input);

        // Assert
        $this->assertInstanceOf(expected: RuntimeProfileData::class, actual: $profile);
        $this->assertSame(expected: 'Mira', actual: $profile->name);
        $this->assertSame(expected: RuntimeRole::Admin, actual: $profile->role);
        $this->assertSame(expected: 'Belgrade', actual: $profile->address->city);
        $this->assertSame(expected: 'Novi Sad', actual: $profile->addresses[0]->city);
    }

    /**
     * @throws JsonException
     */
    public function test_it_removes_hidden_field_when_serializing_data_object() : void
    {
        // Arrange
        $profile = DataTransfer::create(class: RuntimeProfileData::class, input: [
            'user_name' => 'Mira',
            'role'      => 'user',
            'address'   => ['city' => 'Belgrade'],
            'addresses' => [],
            'secret'    => 'hashed',
        ]);

        // Act
        $array = DataTransfer::toArray(object: $profile);

        // Assert
        $this->assertArrayNotHasKey(key: 'secret', array: $array);
        $this->assertSame(expected: 'user', actual: $array['role']);
    }

    /**
     * @throws JsonException
     */
    public function test_it_returns_json_outputs_when_serializing_data_object() : void
    {
        // Arrange
        $profile = DataTransfer::create(class: RuntimeProfileData::class, input: [
            'user_name' => 'Mira',
            'role'      => 'user',
            'address'   => ['city' => 'Belgrade'],
            'addresses' => [],
            'secret'    => 'hashed',
        ]);

        // Act
        $json     = DataTransfer::toJson(object: $profile);
        $jsonApi  = DataTransfer::toJsonApi(object: $profile, type: 'runtime-profile');
        $stdClass = DataTransfer::toStdClass(object: $profile);

        // Assert
        $this->assertJson($json);
        $this->assertSame(expected: 'runtime-profile', actual: $jsonApi['data']['type']);
        $this->assertSame(expected: 'Mira', actual: $stdClass->name);
    }

    public function test_it_returns_structured_failure_when_unknown_field_is_rejected() : void
    {
        // Arrange
        $input = [
            'city'    => 'Belgrade',
            'unknown' => 'value',
        ];

        // Act
        $result = DataTransfer::tryCreate(class: RuntimeAddressData::class, input: $input);

        // Assert
        $this->assertTrue(condition: $result->isFailure());
        $this->assertSame(
            expected: ['unknown' => 'Unknown field "unknown" for Avax\Tests\Foundation\DataHandling\DataTransfer\RuntimeAddressData.'],
            actual  : $result->failureReason()->violations()->toLegacyErrors(),
        );
    }

    public function test_it_casts_docblock_list_items_when_public_property_declares_item_type() : void
    {
        // Arrange
        $input = [
            'addresses' => [
                ['city' => 'Nis'],
            ],
        ];

        // Act
        $data = DataTransfer::create(class: RuntimeDocumentedListData::class, input: $input);

        // Assert
        $this->assertInstanceOf(expected: RuntimeAddressData::class, actual: $data->addresses[0]);
    }

    public function test_it_hydrates_legacy_abstract_dto_when_input_is_valid() : void
    {
        // Arrange
        $input = [
            'email' => 'ada@example.com',
            'name'  => 'Ada',
        ];

        // Act
        $dto = new RuntimeLegacyUserDTO(data: $input);

        // Assert
        $this->assertSame(expected: 'ada@example.com', actual: $dto->email);
        $this->assertSame(expected: ['email' => 'ada@example.com', 'name' => 'Ada'], actual: $dto->toArray());
    }

    public function test_it_throws_dto_validation_exception_when_legacy_input_fails_rules() : void
    {
        // Arrange
        $input = [
            'email' => 'not-an-email',
            'name'  => 'A',
        ];

        // Assert
        $this->expectException(exception: DTOValidationException::class);

        // Act
        new RuntimeLegacyUserDTO(data: $input);
    }

    public function test_it_creates_legacy_public_property_dto_when_data_transfer_targets_legacy_class() : void
    {
        // Arrange
        $input = [
            'email' => 'grace@example.com',
            'name'  => 'Grace',
        ];

        // Act
        $dto = DataTransfer::create(class: RuntimeLegacyUserDTO::class, input: $input);

        // Assert
        $this->assertInstanceOf(expected: RuntimeLegacyUserDTO::class, actual: $dto);
        $this->assertSame(expected: 'Grace', actual: $dto->name);
    }
}

enum RuntimeRole: string
{
    case Admin = 'admin';
    case User  = 'user';
}

final readonly class RuntimeAddressData
{
    public function __construct(public string $city) {}
}

final readonly class RuntimeProfileData
{
    /**
     * @param RuntimeAddressData[] $addresses
     */
    public function __construct(
        #[MapFrom(name: 'user_name')]
        #[CastWith(casterClass: TrimStringCaster::class)]
        public string             $name,
        public RuntimeRole        $role,
        public RuntimeAddressData $address,
        #[ListOf(class: RuntimeAddressData::class)]
        public array              $addresses,
        #[Hidden]
        public string             $secret,
    ) {}
}

final class RuntimeDocumentedListData
{
    /** @var RuntimeAddressData[] */
    public array $addresses;
}

final readonly class TrimStringCaster implements ValueCasterInterface
{
    public function cast(mixed $value, DataField $field, ValueConversionContext $context) : mixed
    {
        return is_string(value: $value) ? trim(string: $value) : $value;
    }
}

final class RuntimeLegacyUserDTO extends AbstractDTO
{
    #[EmailRule]
    public string $email;

    #[MinLengthRule(length: 2)]
    public string $name;
}
