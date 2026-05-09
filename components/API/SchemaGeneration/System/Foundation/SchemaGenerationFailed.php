<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Foundation;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferFailure;
use LogicException;

final class SchemaGenerationFailed extends LogicException
{
    public static function fromDataTransferFailure(DataTransferFailure $failure) : self
    {
        return new self(
            message: 'Schema generation failed: ' . $failure->getMessage(),
            code   : 0,
            previous: $failure,
        );
    }

    public static function unsupportedType(string $type, string $context) : self
    {
        return new self(
            message: "Cannot convert type '{$type}' to JSON Schema in {$context}.",
        );
    }

    public static function missingDataShape(string $class) : self
    {
        return new self(
            message: "Cannot read data shape for '{$class}'. Class does not exist or is not a DataObject.",
        );
    }
}
