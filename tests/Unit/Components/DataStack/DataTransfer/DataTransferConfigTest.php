<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer;

use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use Avax\Components\DataStack\DataTransfer\System\Configuration\UnknownFieldPolicy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class DataTransferConfigTest extends TestCase
{
    #[Test]
    public function default_config_has_expected_defaults() : void
    {
        $config = DataTransferConfig::default();

        $this->assertSame(UnknownFieldPolicy::Reject, $config->unknownFieldPolicy);
        $this->assertTrue($config->allowPublicPropertyHydration);
        $this->assertFalse($config->collectUnknownFields);
        $this->assertSame(32, $config->maxDepth);
    }

    #[Test]
    public function legacy_config_uses_ignore_policy() : void
    {
        $config = DataTransferConfig::legacy();

        $this->assertSame(UnknownFieldPolicy::Ignore, $config->unknownFieldPolicy);
    }

    #[Test]
    public function custom_config_accepts_all_parameters() : void
    {
        $namingPolicy = static fn (string $name) : string => strtolower($name);

        $config = new DataTransferConfig(
            unknownFieldPolicy          : UnknownFieldPolicy::Collect,
            allowPublicPropertyHydration: false,
            collectUnknownFields        : true,
            maxDepth                    : 16,
            namingPolicy                : $namingPolicy,
        );

        $this->assertSame(UnknownFieldPolicy::Collect, $config->unknownFieldPolicy);
        $this->assertFalse($config->allowPublicPropertyHydration);
        $this->assertTrue($config->collectUnknownFields);
        $this->assertSame(16, $config->maxDepth);
    }

    #[Test]
    public function input_name_without_naming_policy_returns_field_name() : void
    {
        $config = DataTransferConfig::default();

        $this->assertSame('firstName', $config->inputNameFor('firstName'));
        $this->assertSame('email', $config->inputNameFor('email'));
    }

    #[Test]
    public function input_name_with_naming_policy_transforms_name() : void
    {
        $config = new DataTransferConfig(
            namingPolicy: static fn (string $name) : string => strtolower($name),
        );

        $this->assertSame('firstname', $config->inputNameFor('firstName'));
    }

    #[Test]
    public function input_name_with_snake_case_policy() : void
    {
        $snakeCase = static function (string $name) : string {
            return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name) ?? $name);
        };

        $config = new DataTransferConfig(namingPolicy: $snakeCase);

        $this->assertSame('first_name', $config->inputNameFor('firstName'));
        $this->assertSame('user_email', $config->inputNameFor('userEmail'));
    }

    #[Test]
    public function with_unknown_field_policy_returns_new_instance() : void
    {
        $original = DataTransferConfig::default();
        $modified = $original->withUnknownFieldPolicy(UnknownFieldPolicy::Collect);

        $this->assertNotSame($original, $modified);
        $this->assertSame(UnknownFieldPolicy::Reject, $original->unknownFieldPolicy);
        $this->assertSame(UnknownFieldPolicy::Collect, $modified->unknownFieldPolicy);
    }

    #[Test]
    public function with_unknown_field_policy_preserves_other_settings() : void
    {
        $original = new DataTransferConfig(
            allowPublicPropertyHydration: false,
            maxDepth                    : 10,
        );

        $modified = $original->withUnknownFieldPolicy(UnknownFieldPolicy::Ignore);

        $this->assertFalse($modified->allowPublicPropertyHydration);
        $this->assertSame(10, $modified->maxDepth);
    }

    #[Test]
    public function with_naming_policy_returns_new_instance() : void
    {
        $original = DataTransferConfig::default();
        $modified = $original->withNamingPolicy(
            static fn (string $name) : string => 'prefix_' . $name,
        );

        $this->assertNotSame($original, $modified);
        $this->assertSame('prefix_test', $modified->inputNameFor('test'));
        $this->assertSame('test', $original->inputNameFor('test'));
    }

    #[Test]
    public function with_naming_policy_preserves_other_settings() : void
    {
        $original = new DataTransferConfig(
            unknownFieldPolicy: UnknownFieldPolicy::Collect,
            maxDepth          : 8,
        );

        $modified = $original->withNamingPolicy(static fn (string $n) => $n);

        $this->assertSame(UnknownFieldPolicy::Collect, $modified->unknownFieldPolicy);
        $this->assertSame(8, $modified->maxDepth);
    }

    #[Test]
    public function with_value_caster_registers_caster() : void
    {
        $config   = DataTransferConfig::default();
        $modified = $config->withValueCaster('DateTime', TestDateTimeCaster::class);

        $this->assertNotNull($modified->casterFor('DateTime'));
        $this->assertNull($config->casterFor('DateTime'));
    }

    #[Test]
    public function with_value_caster_preserves_other_settings() : void
    {
        $original = new DataTransferConfig(maxDepth: 5);
        $modified = $original->withValueCaster('SomeClass', 'some-caster');

        $this->assertSame(5, $modified->maxDepth);
        $this->assertSame('some-caster', $modified->casterFor('SomeClass'));
    }

    #[Test]
    public function with_value_caster_can_use_callable() : void
    {
        $callable = static fn (mixed $v) : mixed => $v;

        $config = DataTransferConfig::default()->withValueCaster('SomeType', $callable);

        $this->assertSame($callable, $config->casterFor('SomeType'));
    }

    #[Test]
    public function with_value_caster_can_use_object() : void
    {
        $caster = new TestDateTimeCaster();

        $config = DataTransferConfig::default()->withValueCaster('DateTime', $caster);

        $this->assertSame($caster, $config->casterFor('DateTime'));
    }

    #[Test]
    public function with_validation_rule_registers_rule() : void
    {
        $config   = DataTransferConfig::default();
        $rule     = new TestValidationRule();
        $modified = $config->withValidationRule('CustomAttr', $rule);

        $this->assertSame($rule, $modified->validationRuleFor('CustomAttr'));
        $this->assertNull($config->validationRuleFor('CustomAttr'));
    }

    #[Test]
    public function with_validation_rule_preserves_other_settings() : void
    {
        $original = new DataTransferConfig(
            unknownFieldPolicy: UnknownFieldPolicy::Reject,
            maxDepth          : 20,
        );

        $modified = $original->withValidationRule('Attr', new TestValidationRule());

        $this->assertSame(UnknownFieldPolicy::Reject, $modified->unknownFieldPolicy);
        $this->assertSame(20, $modified->maxDepth);
    }

    #[Test]
    public function with_validation_rule_can_use_callable() : void
    {
        $callable = static function () : void {};

        $config = DataTransferConfig::default()->withValidationRule('Attr', $callable);

        $this->assertSame($callable, $config->validationRuleFor('Attr'));
    }

    #[Test]
    public function caster_for_returns_null_for_unknown_class() : void
    {
        $config = DataTransferConfig::default();

        $this->assertNull($config->casterFor('UnknownClass'));
    }

    #[Test]
    public function validation_rule_for_returns_null_for_unknown_attribute() : void
    {
        $config = DataTransferConfig::default();

        $this->assertNull($config->validationRuleFor('UnknownAttr'));
    }

    #[Test]
    public function config_is_readonly() : void
    {
        $reflection = new ReflectionClass(DataTransferConfig::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    #[Test]
    public function config_is_readonly_class() : void
    {
        $reflection = new ReflectionClass(DataTransferConfig::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    #[Test]
    public function unknown_field_policy_enum_has_expected_cases() : void
    {
        $cases = UnknownFieldPolicy::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(UnknownFieldPolicy::Reject, $cases);
        $this->assertContains(UnknownFieldPolicy::Ignore, $cases);
        $this->assertContains(UnknownFieldPolicy::Collect, $cases);
    }

    #[Test]
    public function unknown_field_policy_has_string_backing() : void
    {
        $this->assertSame('reject', UnknownFieldPolicy::Reject->value);
        $this->assertSame('ignore', UnknownFieldPolicy::Ignore->value);
        $this->assertSame('collect', UnknownFieldPolicy::Collect->value);
    }

    #[Test]
    public function chaining_withers_produces_correct_config() : void
    {
        $config = DataTransferConfig::default()
            ->withUnknownFieldPolicy(UnknownFieldPolicy::Ignore)
            ->withNamingPolicy(static fn (string $n) => strtolower($n))
            ->withValueCaster('DateTime', TestDateTimeCaster::class);

        $this->assertSame(UnknownFieldPolicy::Ignore, $config->unknownFieldPolicy);
        $this->assertSame('firstname', $config->inputNameFor('firstName'));
        $this->assertSame(TestDateTimeCaster::class, $config->casterFor('DateTime'));
    }

    #[Test]
    public function max_depth_can_be_configured() : void
    {
        $config = new DataTransferConfig(maxDepth: 5);

        $this->assertSame(5, $config->maxDepth);
    }

    #[Test]
    public function max_depth_default_is_32() : void
    {
        $config = DataTransferConfig::default();

        $this->assertSame(32, $config->maxDepth);
    }

    #[Test]
    public function config_handles_multiple_value_casters() : void
    {
        $config = DataTransferConfig::default()
            ->withValueCaster('DateTime', 'caster1')
            ->withValueCaster('Money', 'caster2');

        $this->assertSame('caster1', $config->casterFor('DateTime'));
        $this->assertSame('caster2', $config->casterFor('Money'));
    }

    #[Test]
    public function config_handles_multiple_validation_rules() : void
    {
        $rule1 = new TestValidationRule();
        $rule2 = new TestValidationRule();

        $config = DataTransferConfig::default()
            ->withValidationRule('RuleA', $rule1)
            ->withValidationRule('RuleB', $rule2);

        $this->assertSame($rule1, $config->validationRuleFor('RuleA'));
        $this->assertSame($rule2, $config->validationRuleFor('RuleB'));
    }
}

// -- Test helpers --

final class TestDateTimeCaster
{
    public function cast(mixed $value, string $field) : mixed
    {
        return $value;
    }
}

final class TestValidationRule
{
    public function validate(mixed $value, string $field) : void {}
}
