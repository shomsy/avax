<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\CreateDataObject;

use Avax\DataFoundation\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure;
use Avax\DataFoundation\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation;
use Avax\DataFoundation\DataTransfer\Capabilities\ErrorReporting\DataTransferViolations;
use Throwable;

final readonly class ReportDataObjectCreationFailure
{
    public function report(Throwable $exception) : DataTransferFailure
    {
        if ($exception instanceof DataTransferFailure) {
            return $exception;
        }

        return new DataTransferFailure(
            message   : 'Data object creation failed.',
            violations: DataTransferViolations::from(violations: [
                                                                     new DataTransferViolation(
                                                                         path      : '$',
                                                                         code      : 'data_object_creation_failed',
                                                                         message   : $exception->getMessage(),
                                                                         actualType: $exception::class,
                                                                         previous  : $exception,
                                                                     ),
                                                                 ]),
            previous  : $exception,
        );
    }
}
