<?php

declare(strict_types=1);

namespace Avax\HTTP\Context;

/**
 * Read-only access to PHP runtime globals.
 *
 * This is intentionally minimal and unnormalized.
 */
interface GlobalsProviderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function server(): array;

    /**
     * @return array<string, mixed>
     */
    public function query(): array;

    /**
     * @return array<string, mixed>
     */
    public function post(): array;

    /**
     * @return array<string, mixed>
     */
    public function cookies(): array;

    /**
     * @return array<string, mixed>
     */
    public function files(): array;

    /**
     * @return array<string, mixed>
     */
    public function session(): array;
}
