<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\CreateDataObject;

use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation;
use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolations;
use Avax\DataHandling\DataTransfer\Capabilities\FieldValidation\DataValidationFailed;
use Avax\DataHandling\DataTransfer\Compatibility\LegacyAbstractDTO;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataShape;
use ReflectionClass;
use Throwable;

final readonly class InstantiateDataObject
{
    public function instantiate(DataShape $shape, array $values) : object
    {
        try {
            $reflection      = new ReflectionClass(objectOrClass: $shape->class);
            $constructorArgs = [];

            foreach ($shape->constructorFields() as $field) {
                if (array_key_exists(key: $field->name, array: $values)) {
                    $constructorArgs[] = $values[$field->name]['value'];
                }
            }

            $object = is_a(object_or_class: $shape->class, class: LegacyAbstractDTO::class, allow_string: true)
                ? $reflection->newInstanceWithoutConstructor()
                : $reflection->newInstanceArgs(args: $constructorArgs);

            foreach ($shape->publicPropertyFields() as $field) {
                if ($field->isConstructorField || ! array_key_exists(key: $field->name, array: $values)) {
                    continue;
                }

                $object->{$field->name} = $values[$field->name]['value'];
            }

            return $object;
        } catch (Throwable $exception) {
            throw new DataValidationFailed(
                message   : 'Data object instantiation failed.',
                violations: DataTransferViolations::from(violations: [
                                                             new DataTransferViolation(
                                                                 path        : '$',
                                                                 code        : 'data_object_instantiation_failed',
                                                                 message     : $exception->getMessage(),
                                                                 expectedType: $shape->class,
                                                                 failedRule  : self::class,
                                                                 previous    : $exception,
                                                             ),
                                                         ]),
                previous  : $exception,
            );
        }
    }
}
