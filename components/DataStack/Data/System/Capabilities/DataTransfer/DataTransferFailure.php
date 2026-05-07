<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataTransfer;

use RuntimeException;
use Throwable;

class DataTransferFailure extends RuntimeException
{
    public function __construct(
        string                         $message = '',
        public ?DataTransferViolations $violations = null,
        int                            $code = 0,
        ?Throwable                     $previous = null,
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
