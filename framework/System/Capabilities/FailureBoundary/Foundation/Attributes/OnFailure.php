<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes;

use Attribute;

/**
 * OnFailure — Declares how a specific exception type should be handled.
 *
 * Usage:
 * #[OnFailure(ValidationFailed::class, respondWith: 422)]
 * #[OnFailure(AuthenticationFailed::class, respondWith: 401, messageKey: 'auth.unauthorized')]
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class OnFailure
{
    public function __construct(
        /** @var class-string<\Throwable> */
        public string $exceptionClass,
        public int|null $respondWith = null,
        public string|null $messageKey = null,
    ) {
    }
}
