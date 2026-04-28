<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\DataTransfer;

use JsonSerializable;

/**
 * Single validation violation for DTO
 */
final readonly class DataTransferViolation implements JsonSerializable
{
    public function __construct(
        public string $field,
        public string $message,
        public string|null $code = null,
        public mixed $invalidValue = null,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'field' => $this->field,
            'message' => $this->message,
            'code' => $this->code,
            'invalidValue' => $this->invalidValue,
        ];
    }
}