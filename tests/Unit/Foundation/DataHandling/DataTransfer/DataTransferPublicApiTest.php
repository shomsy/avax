<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Foundation\DataHandling\DataTransfer;

use components\DataHandling\DataTransfer\Capabilities\Attributes\Hidden;
use components\DataHandling\DataTransfer\Capabilities\Attributes\ListOf;
use components\DataHandling\DataTransfer\Capabilities\Attributes\MapFrom;
use components\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure;
use components\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use components\DataHandling\DataTransfer\Configuration\UnknownFieldPolicy;
use components\DataHandling\DataTransfer\DataTransfer;
use PHPUnit\Framework\TestCase;
use SensitiveParameter;

final class DataTransferPublicApiTest extends TestCase
{
    public function test_it_creates_constructor_data_object_when_input_matches_declared_shape() : void
    {
        // Arrange
        $input = [
            'user_name' => 'Mila',
            'role'    => 'admin',
            'address' => ['city' => 'Belgrade'],
            'addresses' => [
                ['city' => 'Novi Sad'],
            ],
            'secret' => 'hidden-value',
        ];

        // Act
        $data = DataTransfer::create(class: UnitProfileData::class, input: $input);

        // Assert
        self::assertInstanceOf(expected: UnitProfileData::class, actual: $data);
        self::assertSame(expected: 'Mila', actual: $data->name);
        self::assertSame(expected: UnitRole::Admin, actual: $data->role);
        self::assertSame(expected: 'Belgrade', actual: $data->address->city);
        self::assertSame(expected: 'Novi Sad', actual: $data->addresses[0]->city);
    }

    public function test_it_serializes_without_hidden_field_when_field_is_marked_hidden() : void
    {
        // Arrange
        $data = new UnitProfileData(
            name     : 'Mila',
            role     : UnitRole::User,
            address  : new UnitAddressData(city: 'Belgrade'),
            addresses: [],
            secret   : 'hidden-value',
        );

        // Act
        $array = DataTransfer::toArray(object: $data);

        // Assert
        self::assertSame(expected: 'Mila', actual: $array['name']);
        self::assertSame(expected: 'user', actual: $array['role']);
        self::assertArrayNotHasKey(key: 'secret', array: $array);
    }

    public function test_it_returns_failure_result_when_unknown_field_is_rejected() : void
    {
        // Arrange
        $input = [
            'city' => 'Belgrade',
            'unknown' => 'value',
        ];

        // Act
        $result = DataTransfer::tryCreate(class: UnitAddressData::class, input: $input);

        // Assert
        self::assertTrue(condition: $result->isFailure());
        self::assertSame(
            expected: ['unknown' => 'Unknown field "unknown" for Avax\Tests\Unit\Foundation\DataHandling\DataTransfer\UnitAddressData.'],
            actual  : $result->failureReason()->violations()->toLegacyErrors(),
        );
    }

    public function test_it_ignores_unknown_field_when_config_policy_allows_ignore() : void
    {
        // Arrange
        $config = DataTransferConfig::default()->withUnknownFieldPolicy(policy: UnknownFieldPolicy::Ignore);

        // Act
        $data = DataTransfer::create(
            class : UnitAddressData::class,
            input : ['city' => 'Belgrade', 'unknown' => 'value'],
            config: $config,
        );

        // Assert
        self::assertSame(expected: 'Belgrade', actual: $data->city);
    }

    public function test_it_preserves_zero_and_false_when_scalar_values_are_boundary_values() : void
    {
        // Arrange
        $input = [
            'count' => '0',
            'active' => 'false',
        ];

        // Act
        $data = DataTransfer::create(class: UnitScalarBoundaryData::class, input: $input);

        // Assert
        self::assertSame(expected: 0, actual: $data->count);
        self::assertFalse(condition: $data->active);
    }

    public function test_it_throws_data_transfer_failure_when_enum_value_is_not_supported() : void
    {
        // Arrange
        $input = [
            'role' => 'owner',
        ];

        // Assert
        $this->expectException(exception: DataTransferFailure::class);

        // Act
        DataTransfer::create(class: UnitRoleData::class, input: $input);
    }

    public function test_it_builds_data_object_when_fluent_for_api_is_used() : void
    {
        // Arrange
        $builder = DataTransfer::for(class: UnitAddressData::class);

        // Act
        $data = $builder->create(input: ['city' => 'Belgrade']);

        // Assert
        self::assertInstanceOf(expected: UnitAddressData::class, actual: $data);
        self::assertSame(expected: 'Belgrade', actual: $data->city);
    }
}

enum UnitRole: string
{
    case Admin = 'admin';
    case User = 'user';
}

final readonly class UnitAddressData
{
    public function __construct(public string $city) {}
}

final readonly class UnitRoleData
{
    public function __construct(public UnitRole $role) {}
}

final readonly class UnitScalarBoundaryData
{
    public function __construct(
        public int $count,
        public bool $active,
    ) {}
}

final readonly class UnitProfileData
{
    /**
     * @param UnitAddressData[] $addresses
     */
    public function __construct(
        #[MapFrom(name: 'user_name')]
        public string   $name,
        public UnitRole $role,
        #[SensitiveParameter]
        public UnitAddressData $address,
        #[SensitiveParameter]
        #[ListOf(class: UnitAddressData::class)]
        public array    $addresses,
        #[SensitiveParameter]
        #[Hidden]
        public string   $secret,
    ) {}
}
