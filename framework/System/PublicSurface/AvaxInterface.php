<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeState;
use Avax\Framework\System\Capabilities\StateReset\StateResetReport;
use Avax\Framework\System\PublicSurface\Console\ConsoleKernelInterface;
use Avax\Framework\System\PublicSurface\Http\HttpKernelInterface;
use Avax\Framework\System\PublicSurface\Runtime\RuntimeKernelInterface;

interface AvaxInterface
{
    public function state(): RuntimeState;

    public function context(): RuntimeContext;

    public function requestScopes(): RequestScopeStore;

    public function components(): ComponentRegistry;

    public function http(): HttpKernelInterface;

    public function console(): ConsoleKernelInterface;

    public function runtime(): RuntimeKernelInterface;

    public function resetState(): StateResetReport;
}
