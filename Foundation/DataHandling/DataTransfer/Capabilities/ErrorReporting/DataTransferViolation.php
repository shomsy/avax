<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting;

use JsonSerializable;
use SensitiveParameter;
use Throwable;

final readonly class DataTransferViolation implements JsonSerializable
{
    public function __construct(
        public string                       $path,
        #[SensitiveParameter] public string $code,
        public string                       $message,
        public string|null                  $expectedType = null,
        public string|null                  $actualType = null,
        public string|null                  $failedRule = null,
        public Throwable|null               $previous = null,
    ) {}

    public function jsonSerialize() : array
    {
        return [
            'path'          => $this->path,
            'code'          => $this->code,
            'message'       => $this->message,
            'expected_type' => $this->expectedType,
            'actual_type'   => $this->actualType,
            'failed_rule'   => $this->failedRule,
        ];
    }
}
