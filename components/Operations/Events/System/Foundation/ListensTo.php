<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Foundation;

use Attribute;

/**
 * Declares that a class listens to a specific event type.
 *
 * Used as a declaration-only attribute. The runtime dispatch
 * does not scan attributes — they are compiled at boot time
 * into the CompiledListenerRegistry.
 *
 * Usage:
 *   #[ListensTo(UserRegistered::class)]
 *   final readonly class SendWelcomeEmail
 *   {
 *       public function __invoke(UserRegistered $event): void { ... }
 *   }
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ListensTo
{
    /**
     * @param class-string $eventClass
     * @param int $priority Higher = earlier execution. Default 0.
     */
    public function __construct(
        public string $eventClass,
        public int $priority = 0,
    ) {
    }
}
