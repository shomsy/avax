<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\SerializeWorkPayload\Failure;

use RuntimeException;
use Throwable;

final class UnserializableWorkException extends RuntimeException
{
    /**
     * @param list<string|int> $names
     */
    public function __construct(
        string       $message,
        public array $names,
        int          $code = 0,
        ?Throwable   $previous = null,
    )
    {
        parent::__construct(message: $message, code: $code, previous: $previous);
    }

    /**
     * @return list<string|int>
     */
    public function getNames() : array
    {
        return $this->names;
    }
}
