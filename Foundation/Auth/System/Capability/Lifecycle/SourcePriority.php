<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Lifecycle;

/**
 * Source priority for lifecycle resolution.
 */
enum SourcePriority: string
{
    case LOCAL = 'local';
    case FEDERATION = 'federation';
    case SCIM = 'scim';
}

/**
 * Source of identity.
 */
enum Source: string
{
    case LOCAL = 'local';
    case FEDERATION = 'federation';
    case SCIM = 'scim';
    case ADMIN = 'admin';
}