<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Foundation;

use Throwable;

final readonly class ParallelFailure
{
    /**
     * @param string|int $name
     */
    public function __construct(
        public string|int $name,
        public string     $message,
        public int        $code,
        public ?Throwable $previous = null,
    ) {}

    public function getName() : string|int
    {
        return $this->name;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function getCode() : int
    {
        return $this->code;
    }

    public function getPrevious() : ?Throwable
    {
        return $this->previous;
    }
}
