<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer;

use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure;
use Avax\DataHandling\DataTransfer\Configuration\DataTransferBuilder;
use Avax\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataHandling\DataTransfer\CreateDataObject\CreateDataObject;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataShape;
use Avax\DataHandling\DataTransfer\InspectDataShape\InspectDataShape;
use Avax\DataHandling\DataTransfer\SerializeDataObject\SerializeDataObject;
use Throwable;

final class DataTransfer
{
    private static DataTransferConfig|null $config = null;

    public static function configure(DataTransferConfig $config) : void
    {
        self::$config = $config;
    }

    public static function config() : DataTransferConfig
    {
        return self::$config ??= DataTransferConfig::default();
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function create(string $class, array|object $input, DataTransferConfig|null $config = null) : object
    {
        return new CreateDataObject(config: $config ?? self::config())->create(class: $class, input: $input);
    }

    /**
     * @param class-string $class
     */
    public static function tryCreate(string $class, array|object $input, DataTransferConfig|null $config = null) : DataTransferResult
    {
        try {
            return DataTransferResult::success(object: self::create(class: $class, input: $input, config: $config));
        } catch (DataTransferFailure $failure) {
            return DataTransferResult::failure(failure: $failure);
        } catch (Throwable $exception) {
            return DataTransferResult::failure(
                failure: new DataTransferFailure(message: 'Data transfer failed.', previous: $exception),
            );
        }
    }

    /**
     * @param class-string $class
     */
    public static function for(string $class, DataTransferConfig|null $config = null) : DataTransferBuilder
    {
        return new DataTransferBuilder(class: $class, config: $config ?? self::config());
    }

    /**
     * @param class-string $class
     */
    public static function inspect(string $class, DataTransferConfig|null $config = null) : DataShape
    {
        return new InspectDataShape(config: $config ?? self::config())->inspect(class: $class);
    }

    public static function toArray(object $object, int|null $depth = null, bool|null $excludeHidden = null, DataTransferConfig|null $config = null) : array
    {
        $excludeHidden ??= true;

        return new SerializeDataObject(config: $config ?? self::config())->toArray(
            object       : $object,
            depth        : $depth,
            excludeHidden: $excludeHidden,
        );
    }

    public static function toJson(object $object, int|null $flags = null, int|null $depth = null, DataTransferConfig|null $config = null) : string
    {
        $flags ??= 0;
        $depth ??= 512;

        return new SerializeDataObject(config: $config ?? self::config())->toJson(object: $object, flags: $flags, depth: $depth);
    }

    public static function toFlatArray(object $object, DataTransferConfig|null $config = null) : array
    {
        return new SerializeDataObject(config: $config ?? self::config())->toFlatArray(object: $object);
    }

    public static function toStdClass(object $object, DataTransferConfig|null $config = null) : object
    {
        return new SerializeDataObject(config: $config ?? self::config())->toStdClass(object: $object);
    }

    public static function toJsonApi(object $object, string $type, DataTransferConfig|null $config = null) : array
    {
        return new SerializeDataObject(config: $config ?? self::config())->toJsonApi(object: $object, type: $type);
    }
}
