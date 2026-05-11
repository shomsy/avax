<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use DateTimeImmutable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use Throwable;

/**
 * CompileClassAttributes — compiles all attributes on a class to serializable metadata.
 *
 * Captures class-level, property-level, and method-level attributes.
 * Writes atomically to disk with checksum validation and source-change tracking.
 */
final class CompileClassAttributes
{
    private readonly string     $cacheDir;
    private readonly string     $configHash;
    private readonly Filesystem $filesystem;

    public function __construct(
        string      $cacheDir,
        string $configHash, Filesystem|null $filesystem = null,
    )
    {
        $this->cacheDir   = rtrim($cacheDir, '/\\');
        $this->configHash = $configHash;
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    /**
     * Load compiled attribute metadata for a class from disk.
     * Returns null if file is missing, corrupt, or invalid.
     *
     * @param class-string $class
     */
    public function loadMetadata(string $class) : ?CompiledAttributeMetadata
    {
        $path = $this->metadataPathForClass($class);

        if (! $this->filesystem->isFile($path)) {
            return null;
        }

        try {
            $json  = $this->filesystem->read($path);
            $state = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $this->quarantineCorrupt($path);

            return null;
        }

        // Validate format
        if (($state['format'] ?? '') !== CompiledAttributeMetadata::FORMAT) {
            $this->quarantineCorrupt($path);

            return null;
        }

        // Validate metadata version
        if (($state['metadataVersion'] ?? 0) !== CompiledAttributeMetadata::METADATA_VERSION) {
            $this->quarantineCorrupt($path);

            return null;
        }

        // Validate checksum
        $storedChecksum = $state['checksum'] ?? '';
        $checkBody      = $state;
        unset($checkBody['checksum']);

        if (CompiledAttributeMetadata::computeChecksum($checkBody) !== $storedChecksum) {
            $this->quarantineCorrupt($path);

            return null;
        }

        try {
            $metadata = CompiledAttributeMetadata::fromArray($state);
        } catch (RuntimeException) {
            $this->quarantineCorrupt($path);

            return null;
        }

        // Validate config hash
        if (! $metadata->isValidForConfig($this->configHash)) {
            return null;
        }

        // Check source change
        if ($this->hasSourceChanged($class, $metadata)) {
            return null;
        }

        return $metadata;
    }

    private function metadataPathForClass(string $class) : string
    {
        $safeName = str_replace('\\', '_', $class);

        return $this->compiledDirectory() . '/attributes.' . $safeName . '.json';
    }

    private function compiledDirectory() : string
    {
        return $this->cacheDir . '/compiled-attributes';
    }

    private function quarantineCorrupt(string $path) : void
    {
        if (! $this->filesystem->isFile($path)) {
            return;
        }

        $quarantineDir = $this->cacheDir . '/compiled-attributes/quarantine';
        if (! is_dir($quarantineDir)) {
            $this->filesystem->createDirectory($quarantineDir);
        }

        $timestamp = (new DateTimeImmutable())->format('YmdHis');
        $hash      = substr(hash('sha256', $path), 0, 8);
        $basename  = pathinfo($path, PATHINFO_BASENAME);
        $dest      = $quarantineDir . '/' . $timestamp . '.' . $hash . '.' . $basename;

        $this->filesystem->move($path, $dest);
    }

    /**
     * Check if a class's source file has changed since compilation.
     *
     * @param class-string $class
     */
    public function hasSourceChanged(string $class, CompiledAttributeMetadata $metadata) : bool
    {
        $storedMtime = $metadata->sourceMtime;
        if ($storedMtime === 0) {
            return true;
        }

        try {
            if (! class_exists($class)) {
                return true;
            }

            $reflection = new ReflectionClass($class);
            $fileName   = $reflection->getFileName();
            if ($fileName === false || ! is_file($fileName)) {
                return true;
            }

            return filemtime($fileName) !== $storedMtime;
        } catch (ReflectionException) {
            return true;
        }
    }

    /**
     * Compile attributes for multiple classes.
     *
     * @param list<class-string> $classes
     *
     * @return array<string, CompiledAttributeMetadata>
     */
    public function compileMany(array $classes) : array
    {
        $result = [];
        foreach ($classes as $class) {
            $result[$class] = $this->compile($class);
        }

        return $result;
    }

    /**
     * Compile attributes for a single class to disk-based metadata.
     *
     * @template T of object
     * @param class-string<T> $class
     */
    public function compile(string $class) : CompiledAttributeMetadata
    {
        $reflection  = new ReflectionClass($class);
        $fileName    = $reflection->getFileName();
        $sourceMtime = 0;

        if ($fileName !== false && is_file($fileName)) {
            $sourceMtime = filemtime($fileName);
        }

        $classAttributes    = $this->extractClassAttributes($reflection);
        $propertyAttributes = $this->extractPropertyAttributes($reflection);
        $methodAttributes   = $this->extractMethodAttributes($reflection);

        $now = (new DateTimeImmutable())->format('c');

        $body = [
            'format'             => CompiledAttributeMetadata::FORMAT,
            'metadataVersion'    => CompiledAttributeMetadata::METADATA_VERSION,
            'compiledAt'         => $now,
            'configHash'         => $this->configHash,
            'className'          => $class,
            'classAttributes'    => $classAttributes,
            'propertyAttributes' => $propertyAttributes,
            'methodAttributes'   => $methodAttributes,
            'sourceMtime'        => $sourceMtime,
        ];

        $checksum         = CompiledAttributeMetadata::computeChecksum($body);
        $body['checksum'] = $checksum;

        $metadata = CompiledAttributeMetadata::fromArray($body);

        $this->ensureDirectoryExists();
        $this->writeAtomically($this->metadataPathForClass($class), $metadata->toJson());

        return $metadata;
    }

    /**
     * @param ReflectionClass<object> $reflection
     *
     * @return array<string, array<string, mixed>>
     */
    private function extractClassAttributes(ReflectionClass $reflection) : array
    {
        return $this->extractAttributesFromDeclarations($reflection->getAttributes());
    }

    /**
     * @param list<ReflectionAttribute<object>> $attributes
     *
     * @return array<string, array<string, mixed>>
     */
    private function extractAttributesFromDeclarations(array $attributes) : array
    {
        $result = [];
        foreach ($attributes as $attribute) {
            // Skip internal PHP attributes
            $className = $attribute->getName();
            if (str_starts_with($className, 'PHP\\') || str_starts_with($className, 'Attribute')) {
                continue;
            }

            $shortName = (new ReflectionClass($className))->getShortName();
            $args      = $attribute->getArguments();

            // Convert object values in args to class names for serializability
            $mappedArgs = [];
            foreach ($args as $key => $value) {
                $mappedArgs[$key] = is_object($value) ? $value::class : $value;
            }

            $result[$shortName] = $mappedArgs;
        }

        return $result;
    }

    /**
     * @param ReflectionClass<object> $reflection
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function extractPropertyAttributes(ReflectionClass $reflection) : array
    {
        $result = [];
        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $attrs = $this->extractAttributesFromDeclarations($property->getAttributes());
            if ($attrs !== []) {
                $result[$property->getName()] = $attrs;
            }
        }

        return $result;
    }

    /**
     * @param ReflectionClass<object> $reflection
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function extractMethodAttributes(ReflectionClass $reflection) : array
    {
        $result = [];
        foreach ($reflection->getMethods() as $method) {
            $attrs = $this->extractAttributesFromDeclarations($method->getAttributes());
            if ($attrs !== []) {
                $result[$method->getName()] = $attrs;
            }
        }

        return $result;
    }

    private function ensureDirectoryExists() : void
    {
        $dir = $this->compiledDirectory();
        if (! is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }
    }

    private function writeAtomically(string $path, string $body) : void
    {
        $tempPath = $path . '.tmp.' . uniqid('', true);
        $this->filesystem->write($tempPath, $body);
        $this->filesystem->move($tempPath, $path);
    }
}
