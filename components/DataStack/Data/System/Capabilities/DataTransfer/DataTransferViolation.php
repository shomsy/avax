<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataTransfer;

use JsonSerializable;
use Override;

/**
 * Single validation violation for DTO
 */
final readonly class DataTransferViolation implements JsonSerializable
{
    public function __construct(
        public string $field,
        public string $message,
        public ?string $code = null,
        public mixed $invalidValue = null,
    ) {}

    #[Override]
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
