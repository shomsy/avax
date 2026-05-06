<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\ResponseContracts;

final class ErrorResponseContract
{
    /**
     * @param  array<string, mixed>|null  $details
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly string $code,
        public readonly string $message,
        public readonly ?string $description,
        public readonly ?array $details,
    ) {
    }

    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError(): bool
    {
        return $this->statusCode >= 500;
    }
}
