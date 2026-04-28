<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\DataTransfer;

use Avax\Components\Data\System\Capabilities\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure;

final readonly class DataTransferResult
{
    private function __construct(
        private object|null              $object,
        private DataTransferFailure|null $failure,
    ) {}

    public static function success(object $object) : self
    {
        return new self(object: $object, failure: null);
    }

    public static function failure(DataTransferFailure $failure) : self
    {
        return new self(object: null, failure: $failure);
    }

    public function isSuccess() : bool
    {
        return $this->object !== null;
    }

    public function isFailure() : bool
    {
        return $this->failure !== null;
    }

    public function object() : object
    {
        return $this->object ?? throw new DataTransferException(message: 'Data transfer did not produce an object.');
    }

    public function failureReason() : DataTransferFailure
    {
        return $this->failure ?? throw new DataTransferException(message: 'Data transfer completed successfully.');
    }
}
