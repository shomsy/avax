<?php

declare(strict_types=1);
namespace Avax\Container\tests;

use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\Container\Features\Define\Store\ServiceDefinitionEntity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class ServiceDefinitionEntityTest extends TestCase
{
    public function test_valid_entity_creation() : void
    {
        $entity = new ServiceDefinitionEntity(
            id          : 'test-service',
            class       : stdClass::class,
            lifetime    : ServiceLifetime::Singleton,
            config      : ['key' => 'value'],
            tags        : ['test', 'mock'],
            dependencies: ['logger'],
            environment : 'testing'
        );

        $this->assertEquals(expected: 'test-service', actual: $entity->id);
        $this->assertEquals(expected: stdClass::class, actual: $entity->class);
        $this->assertEquals(expected: ServiceLifetime::Singleton, actual: $entity->lifetime);
        $this->assertEquals(expected: ['key' => 'value'], actual: $entity->config);
        $this->assertEquals(expected: ['test', 'mock'], actual: $entity->tags);
        $this->assertEquals(expected: ['logger'], actual: $entity->dependencies);
        $this->assertEquals(expected: 'testing', actual: $entity->environment);
        $this->assertTrue(condition: $entity->isActive);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function test_from_array_creation() : void
    {
        $data = [
            'id'           => 'test-service',
            'class'        => stdClass::class,
            'lifetime'     => 'singleton',
            'config'       => '{"key":"value"}',
            'tags'         => '["test","mock"]',
            'dependencies' => '["logger"]',
            'environment'  => 'testing',
            'is_active'    => '1',
            'created_at'   => '2024-01-01 12:00:00',
            'updated_at'   => '2024-01-01 12:30:00'
        ];

        $entity = ServiceDefinitionEntity::fromArray(data: $data);

        $this->assertEquals(expected: 'test-service', actual: $entity->id);
        $this->assertEquals(expected: stdClass::class, actual: $entity->class);
        $this->assertEquals(expected: ServiceLifetime::Singleton, actual: $entity->lifetime);
        $this->assertEquals(expected: ['key' => 'value'], actual: $entity->config);
        $this->assertEquals(expected: ['test', 'mock'], actual: $entity->tags);
        $this->assertEquals(expected: ['logger'], actual: $entity->dependencies);
        $this->assertTrue(condition: $entity->isActive);
    }

    public function test_to_array_conversion() : void
    {
        $entity = new ServiceDefinitionEntity(
            id      : 'test-service',
            class   : stdClass::class,
            lifetime: ServiceLifetime::Singleton
        );

        $array = $entity->toArray();

        $this->assertEquals(expected: 'test-service', actual: $array['id']);
        $this->assertEquals(expected: stdClass::class, actual: $array['class']);
        $this->assertEquals(expected: 'singleton', actual: $array['lifetime']);
        $this->assertEquals(expected: '[]', actual: $array['config']); // JSON encoded empty array
        $this->assertEquals(expected: '[]', actual: $array['tags']);
        $this->assertEquals(expected: '[]', actual: $array['dependencies']);
        $this->assertNull(actual: $array['environment']);
        $this->assertNull(actual: $array['description']);
        $this->assertTrue(condition: $array['is_active']);
    }

    public function test_empty_id_throws_exception() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Service ID cannot be empty');

        new ServiceDefinitionEntity(
            id      : '',
            class   : stdClass::class,
            lifetime: ServiceLifetime::Singleton
        );
    }

    public function test_empty_class_throws_exception() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Service class cannot be empty');

        new ServiceDefinitionEntity(
            id      : 'test-service',
            class   : '',
            lifetime: ServiceLifetime::Singleton
        );
    }

    public function test_nonexistent_class_throws_exception() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'does not exist');

        new ServiceDefinitionEntity(
            id      : 'test-service',
            class   : 'NonExistentClass12345',
            lifetime: ServiceLifetime::Singleton
        );
    }

    public function test_invalid_dependency_throws_exception() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'All dependencies must be non-empty strings');

        new ServiceDefinitionEntity(
            id          : 'test-service',
            class       : stdClass::class,
            lifetime    : ServiceLifetime::Singleton,
            dependencies: ['valid', '', 'also-valid']
        );
    }

    public function test_has_tag_method() : void
    {
        $entity = new ServiceDefinitionEntity(
            id      : 'test-service',
            class   : stdClass::class,
            lifetime: ServiceLifetime::Singleton,
            tags    : ['cache', 'database']
        );

        $this->assertTrue(condition: $entity->hasTag(tag: 'cache'));
        $this->assertTrue(condition: $entity->hasTag(tag: 'database'));
        $this->assertFalse(condition: $entity->hasTag(tag: 'http'));
    }

    public function test_depends_on_method() : void
    {
        $entity = new ServiceDefinitionEntity(
            id          : 'test-service',
            class       : stdClass::class,
            lifetime    : ServiceLifetime::Singleton,
            dependencies: ['logger', 'cache']
        );

        $this->assertTrue(condition: $entity->dependsOn(serviceId: 'logger'));
        $this->assertTrue(condition: $entity->dependsOn(serviceId: 'cache'));
        $this->assertFalse(condition: $entity->dependsOn(serviceId: 'database'));
    }

    public function test_get_complexity_score() : void
    {
        $simpleEntity = new ServiceDefinitionEntity(
            id      : 'simple',
            class   : stdClass::class,
            lifetime: ServiceLifetime::Transient
        );

        $complexEntity = new ServiceDefinitionEntity(
            id          : 'complex',
            class       : stdClass::class,
            lifetime    : ServiceLifetime::Scoped,
            config      : ['a' => 1, 'b' => 2, 'c' => 3],
            dependencies: ['dep1', 'dep2', 'dep3']
        );

        $this->assertEquals(expected: 1, actual: $simpleEntity->getComplexityScore());
        $this->assertGreaterThan(expected: 10, actual: $complexEntity->getComplexityScore());
    }

    public function test_is_available_in_environment() : void
    {
        $noEnvEntity = new ServiceDefinitionEntity(
            id      : 'service1',
            class   : stdClass::class,
            lifetime: ServiceLifetime::Singleton
        );

        $specificEnvEntity = new ServiceDefinitionEntity(
            id         : 'service2',
            class      : stdClass::class,
            lifetime   : ServiceLifetime::Singleton,
            environment: 'production'
        );

        $this->assertTrue(condition: $noEnvEntity->isAvailableInEnvironment(environment: 'development'));
        $this->assertTrue(condition: $noEnvEntity->isAvailableInEnvironment(environment: 'production'));

        $this->assertTrue(condition: $specificEnvEntity->isAvailableInEnvironment(environment: 'production'));
        $this->assertFalse(condition: $specificEnvEntity->isAvailableInEnvironment(environment: 'development'));
        $this->assertFalse(condition: $specificEnvEntity->isAvailableInEnvironment(environment: null));
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function test_with_updates_creates_new_instance() : void
    {
        $original = new ServiceDefinitionEntity(
            id      : 'test-service',
            class   : stdClass::class,
            lifetime: ServiceLifetime::Singleton,
            tags    : ['old']
        );

        $updated = $original->withUpdates(updates: [
            'tags'        => json_encode(['new']),
            'environment' => 'production'
        ]);

        $this->assertNotSame(expected: $original, actual: $updated);
        $this->assertEquals(expected: ['old'], actual: $original->tags);
        $this->assertEquals(expected: ['new'], actual: $updated->tags);
        $this->assertEquals(expected: 'production', actual: $updated->environment);
    }

    public function test_table_name_constant() : void
    {
        $this->assertEquals(expected: 'container_service_definitions', actual: ServiceDefinitionEntity::getTableName());
    }
}