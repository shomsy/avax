<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation;

enum FeatureFlagState: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
