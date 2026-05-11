<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use RuntimeException;

/**
 * Compiles DataShape inspections to disk-based metadata with atomic writes,
 * checksum validation, corruption quarantine, and source-change invalidation.
 */
final class CompileDataShapeSchema
{
    private readonly string $cacheDir;
    private readonly string $configHash;
    private readonly Filesystem $filesystem;

    public function __construct(
        string $cacheDir,
        string $configHash,
        ?Filesystem $filesystem = null,
    ) {
        $this->cacheDir = rtrim($cacheDir, '/\\');
        $this->configHash = $configHash;
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    /**
     * Compile DataShapes for the given classes into disk-based metadata.
     *
     * @param list<class-string> $classes
     */
    public function compile(
        array $classes,
        InspectDataShape $inspector,
    ): CompiledSchemaMetadata {
        $entries = [];
        $sourceMtimes = [];

        foreach ($classes as $class) {
            $shape = $inspector->inspect($class);
            $entries[$class] = $this->serializeDataShape($shape);

            $reflection = new \ReflectionClass($class);
            $fileName = $reflection->getFileName();
            if ($fileName !== false && is_file($fileName)) {
                $sourceMtimes[$class] = filemtime($fileName);
            }
        }

        $fingerprint = $this->computeFingerprint($classes, $entries);
        $now = (new \DateTimeImmutable())->format('c');

        $body = [
            'format' => CompiledSchemaMetadata::FORMAT,
            'schemaVersion' => CompiledSchemaMetadata::SCHEMA_VERSION,
            'compiledAt' => $now,
            'configHash' => $this->configHash,
            'fingerprint' => $fingerprint,
            'entries' => $entries,
            'sourceMtimes' => $sourceMtimes,
        ];

        $checksum = CompiledSchemaMetadata::computeChecksum($body);
        $body['checksum'] = $checksum;

        $metadata = CompiledSchemaMetadata::fromArray($body);

        $this->ensureDirectoryExists();
        $this->writeAtomically($this->metadataPath(), $metadata->toJson());

        return $metadata;
    }

    /**
     * Load existing compiled metadata from disk.
     * Returns null if file is missing, corrupt, or invalid.
     */
    public function loadMetadata(): ?CompiledSchemaMetadata
    {
        $path = $this->metadataPath();

        if (! $this->filesystem->isFile($path)) {
            return null;
        }

        try {
            $json = $this->filesystem->read($path);
            $state = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            $this->quarantineCorrupt($path);

            return null;
        }

        // Validate format
        if (($state['format'] ?? '') !== CompiledSchemaMetadata::FORMAT) {
            $this->quarantineCorrupt($path);

            return null;
        }

        // Validate schema version
        if (($state['schemaVersion'] ?? 0) !== CompiledSchemaMetadata::SCHEMA_VERSION) {
            $this->quarantineCorrupt($path);

            return null;
        }

        // Validate checksum
        $storedChecksum = $state['checksum'] ?? '';
        $checkBody = $state;
        unset($checkBody['checksum']);

        if (CompiledSchemaMetadata::computeChecksum($checkBody) !== $storedChecksum) {
            $this->quarantineCorrupt($path);

            return null;
        }

        try {
            return CompiledSchemaMetadata::fromArray($state);
        } catch (RuntimeException) {
            $this->quarantineCorrupt($path);

            return null;
        }
    }

    /**
     * Check if a class's source file has changed since compilation.
     */
    public function hasSourceChanged(string $class, CompiledSchemaMetadata $metadata): bool
    {
        $storedMtime = $metadata->sourceMtimes[$class] ?? null;
        if ($storedMtime === null) {
            return true;
        }

        try {
            if (! class_exists($class)) {
                return true;
            }

            $reflection = new \ReflectionClass($class);
            $fileName = $reflection->getFileName();
            if ($fileName === false || ! is_file($fileName)) {
                return true;
            }

            return filemtime($fileName) !== $storedMtime;
        } catch (\ReflectionException) {
            return true;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeDataShape(DataShape $shape): array
    {
        $fields = [];
        foreach ($shape->fields() as $name => $field) {
            $fields[$name] = [
                'name' => $field->name,
                'inputName' => $field->inputName,
                'dataFieldType' => [
                    'names' => $field->dataFieldType->names(),
                    'allowsNull' => $field->dataFieldType->allowsNull,
                ],
                'attributes' => $this->serializeAttributes($field->attributes),
                'isConstructorField' => $field->isConstructorField,
                'isPromotedProperty' => $field->isPromotedProperty,
                'isPublicProperty' => $field->isPublicProperty,
                'hasDefaultValue' => $field->hasDefaultValue,
                'defaultValue' => $field->defaultValue,
            ];
        }

        return [
            'class' => $shape->class,
            'fields' => $fields,
        ];
    }

    /**
     * @param object[] $attributes
     * @return array<string, array<string, mixed>>
     */
    private function serializeAttributes(array $attributes): array
    {
        $result = [];
        foreach ($attributes as $attribute) {
            $className = $attribute::class;
            $shortName = (new \ReflectionClass($attribute))->getShortName();
            $result[$shortName] = $this->extractConstructorArgs($attribute);
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractConstructorArgs(object $object): array
    {
        $reflection = new \ReflectionClass($object);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $propName = $param->getName();
            // Use reflection to get property value
            if ($reflection->hasProperty($propName)) {
                $prop = $reflection->getProperty($propName);
                $value = $prop->getValue($object);
                $args[$propName] = is_object($value) ? $value::class : $value;
            }
        }

        return $args;
    }

    /**
     * @param list<class-string> $classes
     * @param array<string, array<string, mixed>> $entries
     */
    private function computeFingerprint(array $classes, array $entries): string
    {
        sort($classes);

        return sha1(serialize([
            'configHash' => $this->configHash,
            'classes' => $classes,
            'entries' => $entries,
        ]));
    }

    private function writeAtomically(string $path, string $body): void
    {
        $tempPath = $path . '.tmp.' . uniqid('', true);
        $this->filesystem->write($tempPath, $body);
        $this->filesystem->move($tempPath, $path);
    }

    private function quarantineCorrupt(string $path): void
    {
        if (! $this->filesystem->isFile($path)) {
            return;
        }

        $quarantineDir = $this->cacheDir . '/compiled/quarantine';
        if (! is_dir($quarantineDir)) {
            mkdir($quarantineDir, 0o755, true);
        }

        $timestamp = (new \DateTimeImmutable())->format('YmdHis');
        $hash = substr(hash('sha256', $path), 0, 8);
        $basename = pathinfo($path, PATHINFO_BASENAME);
        $dest = $quarantineDir . '/' . $timestamp . '.' . $hash . '.' . $basename;

        $this->filesystem->move($path, $dest);
    }

    private function ensureDirectoryExists(): void
    {
        $dir = $this->compiledDirectory();
        if (! is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }
    }

    private function metadataPath(): string
    {
        return $this->compiledDirectory() . '/schema.json';
    }

    private function compiledDirectory(): string
    {
        return $this->cacheDir . '/compiled';
    }
}
