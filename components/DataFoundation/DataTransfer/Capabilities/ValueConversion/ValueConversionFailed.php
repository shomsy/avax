<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\ValueConversion;

use Avax\DataFoundation\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure;
use Avax\DataFoundation\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation;
use Avax\DataFoundation\DataTransfer\Capabilities\ErrorReporting\DataTransferViolations;
use Throwable;

final class ValueConversionFailed extends DataTransferFailure
{
    public static function forField(
        string         $path,
        string         $expectedType,
        mixed          $actualValue,
        string         $message,
        Throwable|null $previous = null,
    ) : self
    {
        return new self(
            message   : 'Data value conversion failed.',
            violations: DataTransferViolations::from(violations: [
                                                                     new DataTransferViolation(
                                                                         path        : $path,
                                                                         code        : 'value_conversion_failed',
                                                                         message     : $message,
                                                                         expectedType: $expectedType,
                                                                         actualType  : get_debug_type(value: $actualValue),
                                                                         previous    : $previous,
                                                                     ),
                                                                 ]),
            previous  : $previous,
        );
    }
}
