<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\API;

/**
 * ContextResolverInterface
 *
 * Contract for resolving session contexts.
 *
 * @package Avax\HTTP\Session\API
 */
interface ContextResolverInterface
{
    /**
     * Resolve context for user.
     *
     * @param string|int $userId The user ID.
     *
     * @return array<string, mixed>
     */
    public function forUser(string|int $userId): array;

    /**
     * Resolve context for API token.
     *
     * @param string $token The API token.
     *
     * @return array<string, mixed>
     */
    public function forApiToken(string $token): array;

    /**
     * Resolve context for system.
     *
     * @return array<string, mixed>
     */
    public function forSystem(): array;
}
