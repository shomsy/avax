<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\SecureRequest\System\Foundation\Failure;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolations;
use RuntimeException;
use Throwable;

class SecureRequestValidationFailed extends RuntimeException
{
    public function __construct(
        string                         $message = 'SecureRequest validation failed.',
        public DataTransferViolations|null $violations = null,
        int                                $code = 0, Throwable|null $previous = null,
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
