<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Enum;

enum ServiceLifetime : string
{
    /** One instance shared across the entire application lifecycle within the container. */
    case Singleton = 'singleton';

    /** One instance per defined scope; reused within the scope but destroyed when the scope ends. */
    case Scoped = 'scoped';

    /** A new instance created every time the service is requested; no persistence. */
    case Transient = 'transient';
}
