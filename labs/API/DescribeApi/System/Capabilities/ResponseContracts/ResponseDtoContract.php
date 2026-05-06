<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\ResponseContracts;

final class ResponseDtoContract
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly int $statusCode,
        public readonly array $properties = [],
        public readonly ?string $example = null,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }
}
