<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\SecureRequest\System\Capabilities\SecureRequestValidation;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolation;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation\DataTransferViolations;

final class ValidationContext
{
    private DataTransferViolations $violations;

    public function __construct(
        public readonly object  $request,
        /** @var array<string, mixed> */
        public readonly array   $input,
        ?DataTransferViolations $violations = null,
    )
    {
        $this->violations = $violations ?? DataTransferViolations::empty();
    }

    public function addViolation(
        string  $field,
        string  $message,
        ?string $code = null,
        mixed   $invalidValue = null,
    ) : void
    {
        $this->violations = $this->violations->add(
            new DataTransferViolation(
                field       : $field,
                message     : $message,
                code        : $code,
                invalidValue: $invalidValue,
            ),
        );
    }

    public function hasViolations() : bool
    {
        return ! $this->violations->isEmpty();
    }

    public function violations() : DataTransferViolations
    {
        return $this->violations;
    }
}
