<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Closure;

final readonly class ValidateCompiledCachePayload
{
    /**
 * @throws CompiledCachePayloadWasInvalid
 */
public function validate(string $name, mixed $payload): void
    {
        if ($payload instanceof Closure) {
            throw new CompiledCachePayloadWasInvalid(
                reason: sprintf('Compiled cache "%s" returned a closure which is not allowed', $name),
            );
        }

        if (is_resource($payload)) {
            throw new CompiledCachePayloadWasInvalid(
                reason: sprintf('Compiled cache "%s" returned a resource which is not allowed', $name),
            );
        }

        if (is_object($payload) && ! $this->isExportableObject()) {
            throw new CompiledCachePayloadWasInvalid(
                reason: sprintf('Compiled cache "%s" returned an unsupported object type: %s', $name, $payload::class),
            );
        }

        if ($payload === null || is_array($payload) || is_scalar($payload)) {
            return;
        }

        throw new CompiledCachePayloadWasInvalid(
            reason: sprintf('Compiled cache "%s" returned unsupported type: %s', $name, gettype($payload)),
        );
    }

    private function isExportableObject(): bool
    {
        return false;
    }
}
