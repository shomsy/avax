<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core\Results;

/**
 * ErrorDTO
 *
 * Represents a failed action result.
 *
 * @package Avax\HTTP\Session\Core\Results
 */
final readonly class ErrorDTO implements ResultInterface
{
    /**
     * ErrorDTO Constructor.
     *
     * @param string               $message The error message.
     * @param ResultStatus         $status  The result status.
     * @param array<string, mixed> $context Additional context.
     */
    public function __construct(
        public string $message,
        public ResultStatus $status = ResultStatus::FAILURE,
        public array $context = []
    ) {}

    /**
     * {@inheritdoc}
     */
    public function isSuccess(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isError(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): ResultStatus
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->getStatus()->value,
            'message' => $this->message,
            'context' => $this->context,
        ];
    }
}
