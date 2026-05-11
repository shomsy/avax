<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\PublicSurface;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\CacheDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\CompileDataShapeSchema;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\CompiledSchemaMetadata;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShapeCompiler;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferFailure;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferResult;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use Avax\Components\DataStack\DataTransfer\System\Flows\CreateDataObject\CreateDataObject;
use Avax\Components\DataStack\DataTransfer\System\Flows\SerializeDataObject\SerializeDataObject;
use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use stdClass;
use Throwable;

/**
 * DataTransfer — thin stable doorway for the DTO engine.
 *
 * Delegates to:
 * - CreateDataObject flow for hydration/casting/validation
 * - SerializeDataObject flow for serialization
 *
 * Public API:
 * - DataTransfer::create(ClassName::class, $input)
 * - DataTransfer::tryCreate(ClassName::class, $input)
 * - DataTransfer::toArray($object)
 * - DataTransfer::toJson($object)
 * - DataTransfer::toStdClass($object)
 * - DataTransfer::toFlatArray($object)
 * - DataTransfer::toJsonApi($object, $type)
 */
final class DataTransfer implements ResettableState
{
    private static ?DataTransferConfig $dataTransferConfig = null;

    public function resetState() : void
    {
        self::$dataTransferConfig = null;
        CacheDataShape::reset();
        DataShapeCompiler::reset();
    }

    public static function configure(DataTransferConfig $dataTransferConfig) : void
    {
        self::$dataTransferConfig = $dataTransferConfig;
    }

    /**
     * @template T of object
     * @param class-string<T>             $class
     * @param array<string, mixed>|object $input
     */
    public static function tryCreate(string $class, array|object $input) : DataTransferResult
    {
        try {
            return DataTransferResult::success(object: self::create(class: $class, input: $input));
        } catch (DataTransferFailure $failure) {
            return DataTransferResult::failure(dataTransferFailure: $failure);
        } catch (Throwable $exception) {
            return DataTransferResult::failure(
                dataTransferFailure: new DataTransferFailure(
                                         message : 'Data transfer failed.',
                                         previous: $exception,
                                     ),
            );
        }
    }

    /**
     * @template T of object
     * @param class-string<T>             $class
     * @param array<string, mixed>|object $input
     *
     * @return T
     */
    public static function create(string $class, array|object $input) : object
    {
        return (new CreateDataObject(dataTransferConfig: self::config()))->create(class: $class, input: $input);
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(object $object): array
    {
        return (new SerializeDataObject())->toArray(object: $object);
    }

    public static function toJson(object $object, int $flags = 0) : string
    {
        return (new SerializeDataObject())->toJson(object: $object, flags: $flags);
    }

    public static function toStdClass(object $object) : stdClass
    {
        return (new SerializeDataObject())->toStdClass(object: $object);
    }

    /**
     * @return array<string, mixed>
     */
    public static function toFlatArray(object $object): array
    {
        return (new SerializeDataObject())->toFlatArray(object: $object);
    }

    /**
     * @return array{data: array{type: string, id: mixed, attributes: array<string, mixed>}}
     */
    public static function toJsonApi(object $object, string $type): array
    {
        return (new SerializeDataObject())->toJsonApi(object: $object, type: $type);
    }

    public static function config() : DataTransferConfig
    {
        return self::$dataTransferConfig ??= DataTransferConfig::default();
    }

    /**
     * Warmup compiled schema metadata for production.
     *
     * Compiles DataShape metadata for the given classes and writes to disk
     * with atomic writes, checksum validation, and source-change tracking.
     *
     * @param list<class-string> $classes
     */
    public static function warmupSchemaCache(
        array $classes,
        ?string $cacheDir = null,
    ): CompiledSchemaMetadata {
        $config = self::config();
        $dir = $cacheDir ?? $config->schemaCacheDir ?? sys_get_temp_dir() . '/avax-data-transfer-cache';
        $configHash = spl_object_id($config);

        $inspector = new InspectDataShape(dataTransferConfig: $config);
        $compiler = new CompileDataShapeSchema(
            cacheDir: $dir,
            configHash: (string) $configHash,
            filesystem: new Filesystem(),
        );

        return $compiler->compile(classes: $classes, inspector: $inspector);
    }
}
