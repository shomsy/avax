<?php

declare(strict_types=1);

namespace components\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Closure;

final readonly class ValidateCompiledCachePayload
{
    public function validate(string $name, mixed $payload) : void
    {
        if ($payload instanceof Closure) {
            throw new CompiledCachePayloadWasInvalid(
                reason: sprintf('Compiled cache "%s" returned a closure which is not allowed', $name)
            );
        }

        if (is_resource($payload)) {
            throw new CompiledCachePayloadWasInvalid(
                reason: sprintf('Compiled cache "%s" returned a resource which is not allowed', $name)
            );
        }

        if (is_object($payload) && ! $this->isExportableObject(object: $payload)) {
            throw new CompiledCachePayloadWasInvalid(
                reason: sprintf('Compiled cache "%s" returned an unsupported object type: %s', $name, get_class($payload))
            );
        }

        if ($payload === null || is_array($payload) || is_scalar($payload)) {
            return;
        }

        throw new CompiledCachePayloadWasInvalid(
            reason: sprintf('Compiled cache "%s" returned unsupported type: %s', $name, gettype($payload))
        );
    }

    private function isExportableObject(mixed $object) : bool
    {
        return false;
    }
}