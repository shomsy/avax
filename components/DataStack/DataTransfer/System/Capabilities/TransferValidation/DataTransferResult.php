<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\TransferValidation;

final readonly class DataTransferResult
{
    private function __construct(
        private ?object              $object,
        private ?DataTransferFailure $dataTransferFailure,
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

    public function object() : object
    {
        return $this->object ?? throw new DataTransferException(message: 'Data transfer did not produce an object.');
    }

    public function failureReason() : DataTransferFailure
    {
        return $this->dataTransferFailure ?? throw new DataTransferException(message: 'Data transfer completed successfully.');
    }
}
