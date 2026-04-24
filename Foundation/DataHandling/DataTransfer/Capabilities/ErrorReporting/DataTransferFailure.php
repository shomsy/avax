<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting;

use Avax\DataHandling\DataTransfer\DataTransferException;
use Throwable;

class DataTransferFailure extends DataTransferException
{
    private readonly DataTransferViolations $resolvedViolations;

    public function __construct(
        string                      $message = 'Data transfer failed.',
        DataTransferViolations|null $violations = null,
        Throwable|null              $previous = null,
    )
    {
        $resolvedViolations = $violations ?? DataTransferViolations::empty();
        parent::__construct(
            message : new ExplainDataTransferFailure()->explain(
                        violations: $resolvedViolations,
                        fallback  : $message,
                    ),
            previous: $previous,
        );

        $this->resolvedViolations = $resolvedViolations;
    }

    public static function withViolation(
        string         $path,
        string         $code,
        string         $message,
        string|null    $expectedType = null,
        string|null    $actualType = null,
        string|null    $failedRule = null,
        Throwable|null $previous = null,
    ) : self
    {
        return new self(
            violations: DataTransferViolations::from([
                                                         new DataTransferViolation(
                                                             path        : $path,
                                                             code        : $code,
                                                             message     : $message,
                                                             expectedType: $expectedType,
                                                             actualType  : $actualType,
                                                             failedRule  : $failedRule,
                                                             previous    : $previous,
                                                         ),
                                                     ]),
            previous  : $previous,
        );
    }

    public function violations() : DataTransferViolations
    {
        return $this->resolvedViolations;
    }
}
