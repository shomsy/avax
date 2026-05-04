<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\RequestContracts;

use Avax\Labs\API\DescribeApi\System\Foundation\Failure\ApiContractInvalid;

final class RequestValidationResult
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(
        public readonly bool  $valid,
        public readonly array $errors = [],
    )
    {
    }

    public static function valid(): self
    {
        return new self(true);
    }

    /**
     * @param array<string, string> $errors
     */
    public static function invalid(array $errors): self
    {
        return new self(false, $errors);
    }

    public function toException(): ApiContractInvalid|null
    {
        if ($this->valid) {
            return null;
        }
        $reason = json_encode($this->errors);

        return new ApiContractInvalid('Request validation failed', $reason === false ? null : $reason);
    }
}
