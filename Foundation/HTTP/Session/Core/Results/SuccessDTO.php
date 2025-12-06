<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core\Results;

/**
 * SuccessDTO
 *
 * Represents a successful action result.
 *
 * @package Avax\HTTP\Session\Core\Results
 */
final readonly class SuccessDTO implements ResultInterface
{
    /**
     * SuccessDTO Constructor.
     *
     * @param mixed                $data    The result data.
     * @param array<string, mixed> $context Additional context.
     */
    public function __construct(
        public mixed $data = null,
        public array $context = []
    ) {}

    /**
     * {@inheritdoc}
     */
    public function isSuccess(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isError(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): ResultStatus
    {
        return ResultStatus::SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->getStatus()->value,
            'data' => $this->data,
            'context' => $this->context,
        ];
    }
}
