<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation;

use Psr\Http\Message\RequestInterface;

/**
 * FailureContext — Carries execution context through the failure boundary.
 */
final readonly class FailureContext
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public FailureBoundaryKind $kind,
        public string $targetClass = '',
        public string $targetMethod = '',
        public RequestInterface|null $request = null,
        public array $metadata = [],
    ) {
    }

    public static function forHttp(RequestInterface $request, string $targetClass = '', string $targetMethod = ''): self
    {
        return new self(
            kind: FailureBoundaryKind::Http,
            targetClass: $targetClass,
            targetMethod: $targetMethod,
            request: $request,
        );
    }

    public static function forConsole(string $targetClass = '', string $targetMethod = ''): self
    {
        return new self(
            kind: FailureBoundaryKind::Console,
            targetClass: $targetClass,
            targetMethod: $targetMethod,
        );
    }

    public static function forQueue(string $targetClass = '', string $targetMethod = ''): self
    {
        return new self(
            kind: FailureBoundaryKind::Queue,
            targetClass: $targetClass,
            targetMethod: $targetMethod,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public static function custom(FailureBoundaryKind $kind, string $targetClass = '', string $targetMethod = '', array $metadata = []): self
    {
        return new self(
            kind: $kind,
            targetClass: $targetClass,
            targetMethod: $targetMethod,
            metadata: $metadata,
        );
    }

    public function targetKey(): string
    {
        if ($this->targetClass !== '' && $this->targetMethod !== '') {
            return $this->targetClass . '::' . $this->targetMethod;
        }
        return '';
    }
}
