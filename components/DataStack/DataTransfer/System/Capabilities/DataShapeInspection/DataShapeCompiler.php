<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Hidden;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\MapFrom;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Throwable;

/**
 * Resolves DataShape for a class using a 3-tier fallback:
 * 1. Compiled disk metadata (if configured + valid + not stale)
 * 2. In-memory InspectDataShape cache
 * 3. Live ReadClassDataShape reflection (ultimate fallback)
 */
final class DataShapeCompiler
{
    /**
     * @var array<string, DataShape>
     */
    private static array $resolvedCache = [];

    public function __construct(
        private readonly InspectDataShape $inspectDataShape,
        private readonly ?ReadClassDataShape $readClassDataShape = null,
        private readonly ?CompileDataShapeSchema $compileDataShapeSchema = null,
        private readonly ?string $configHash = null,
    ) {}

    /**
     * Resolve DataShape for a class, using compiled metadata when available.
     *
     * @param class-string $class
     */
    public function resolve(string $class, DataTransferConfig|null $config = null) : DataShape
    {
        $configHash = $this->configHash ?? (string) ($config ? spl_object_id($config) : 'default');
        $cacheKey = $class . ':' . $configHash;

        if (isset(self::$resolvedCache[$cacheKey])) {
            return self::$resolvedCache[$cacheKey];
        }

        // Tier 1: Try compiled metadata from disk
        $compiled = $this->tryResolveFromCompiled($class, $configHash);
        if ($compiled !== null) {
            return self::$resolvedCache[$cacheKey] = $compiled;
        }

        // Tier 2: In-memory InspectDataShape cache
        $shape = $this->inspectDataShape->inspect($class);

        return self::$resolvedCache[$cacheKey] = $shape;
    }

    /**
     * Reset static cache for long-lived worker safety.
     */
    public static function reset(): void
    {
        self::$resolvedCache = [];
        CacheDataShape::reset();
    }

    /**
     * @param class-string $class
     */
    private function tryResolveFromCompiled(string $class, string $configHash) : DataShape|null
    {
        if ($this->compileDataShapeSchema === null) {
            return null;
        }

        try {
            $metadata = $this->compileDataShapeSchema->loadMetadata();
            if ($metadata === null) {
                return null;
            }

            if (! $metadata->isValidForConfig($configHash)) {
                return null;
            }

            if ($metadata->hasSchemaVersionMismatch()) {
                return null;
            }

            if ($this->compileDataShapeSchema->hasSourceChanged($class, $metadata)) {
                return null;
            }

            if (! isset($metadata->entries[$class])) {
                return null;
            }

            return $this->deserializeDataShape($metadata->entries[$class]);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Reconstruct DataShape from serialized array.
     *
     * @param array<string, mixed> $entry
     */
    private function deserializeDataShape(array $entry): DataShape
    {
        $fields = [];
        foreach ($entry['fields'] as $name => $fieldData) {
            $attributes = $this->deserializeAttributes($fieldData['attributes']);

            $dataFieldType = new DataFieldType(
                names: $fieldData['dataFieldType']['names'],
                allowsNull: $fieldData['dataFieldType']['allowsNull'],
            );

            $fields[$name] = new DataField(
                name: $fieldData['name'],
                inputName: $fieldData['inputName'],
                dataFieldType: $dataFieldType,
                attributes: $attributes,
                isConstructorField: $fieldData['isConstructorField'],
                isPromotedProperty: $fieldData['isPromotedProperty'],
                isPublicProperty: $fieldData['isPublicProperty'],
                hasDefaultValue: $fieldData['hasDefaultValue'],
                defaultValue: $fieldData['defaultValue'],
                reflectionProperty: $this->tryGetReflectionProperty($entry['class'], $name),
            );
        }

        return new DataShape(
            class: $entry['class'],
            fields: $fields,
        );
    }

    /**
     * Re-instantiate attribute objects from serialized form.
     *
     * @param array<string, array<string, mixed>> $serialized
     * @return object[]
     */
    private function deserializeAttributes(array $serialized): array
    {
        $attributeMap = [
            'Required' => Required::class,
            'Optional' => Optional::class,
            'DefaultValue' => DefaultValue::class,
            'CastWith' => CastWith::class,
            'ListOf' => ListOf::class,
            'MapFrom' => MapFrom::class,
            'Hidden' => Hidden::class,
        ];

        $attributes = [];
        foreach ($serialized as $shortName => $args) {
            $fqcn = $attributeMap[$shortName] ?? null;
            if ($fqcn === null) {
                // Try to resolve by scanning the AttributeReading namespace
                $fqcn = $this->resolveAttributeClass($shortName);
                if ($fqcn === null) {
                    continue;
                }
            }

            $attributes[] = $this->instantiateAttribute($fqcn, $args);
        }

        return $attributes;
    }

    private function resolveAttributeClass(string $shortName) : string|null
    {
        $namespace = 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\AttributeReading\\';
        $fqcn = $namespace . $shortName;

        if (class_exists($fqcn)) {
            return $fqcn;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $args
     */
    private function instantiateAttribute(string $fqcn, array $args): object
    {
        if ($args === []) {
            return new $fqcn();
        }

        // Map known attribute constructor parameter names
        $mapped = [];
        foreach ($args as $key => $value) {
            $mapped[$key] = $value;
        }

        return new $fqcn(...$mapped);
    }

    private function tryGetReflectionProperty(string $class, string $name) : ReflectionProperty|null
    {
        try {
            if (! class_exists($class)) {
                return null;
            }

            $reflection = new ReflectionClass($class);

            return $reflection->hasProperty($name) ? $reflection->getProperty($name) : null;
        } catch (ReflectionException) {
            return null;
        }
    }
}
