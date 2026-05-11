<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation;

final readonly class DataTransferResult
{
    private function __construct(
        private object|null              $object,
        private DataTransferFailure|null $dataTransferFailure,
    ) {}

    public static function success(object $object) : self
    {
        return new self(object: $object, dataTransferFailure: null);
    }

    public static function failure(DataTransferFailure $dataTransferFailure) : self
    {
        return new self(object: null, dataTransferFailure: $dataTransferFailure);
    }

    public function isSuccess() : bool
    {
        return $this->object !== null;
    }

    public function isFailure() : bool
    {
        return $this->dataTransferFailure instanceof DataTransferFailure;
    }

    /**
     * Get the hydrated object.
     *
     * @throws DataTransferException if failure
     */
    public function object() : object
    {
        return $this->object ?? throw new DataTransferException(
            message: 'Data transfer did not produce an object. Call violations() to read validation errors.',
        );
    }

    /**
     * Get validation violations directly.
     *
     * Returns empty violations on success.
     * Returns collected violations on failure.
     */
    public function violations() : DataTransferViolations
    {
        return $this->dataTransferFailure->violations ?? DataTransferViolations::empty();
    }

    /**
     * Check if there are any validation violations.
     */
    public function hasViolations() : bool
    {
        return $this->dataTransferFailure?->violations instanceof DataTransferViolations
            && ! $this->dataTransferFailure->violations->isEmpty();
    }

    /**
     * Get the lower-level failure object.
     *
     * @throws DataTransferException if success
     */
    public function failureReason() : DataTransferFailure
    {
        return $this->dataTransferFailure ?? throw new DataTransferException(
            message: 'Data transfer completed successfully.',
        );
    }
}
