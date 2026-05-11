<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation;

use RuntimeException;
use Throwable;

class DataTransferFailure extends RuntimeException
{
    public function __construct(
        string                         $message = '',
        public DataTransferViolations|null $violations = null,
        int                                $code = 0, Throwable|null $previous = null,
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
