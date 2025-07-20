<?php

declare(strict_types=1);

namespace Gemini\Database\QueryBuilder\Exception;

use Exception;
use Throwable;

class QueryBuilderException extends Exception
{
    public function __construct(
        string         $message = "",
        int            $code = 500,
        Throwable|null $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}