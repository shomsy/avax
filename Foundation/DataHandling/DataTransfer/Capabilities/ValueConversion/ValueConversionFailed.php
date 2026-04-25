<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\ValueConversion;

use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure;
use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation;
use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolations;
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
