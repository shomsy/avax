<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataTransfer;

use InvalidArgumentException;
use Throwable;

class DataTransferException extends InvalidArgumentException
{
    public function __construct(string $message = '', int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct(message: $message, code: $code, previous: $previous);
    }
}
