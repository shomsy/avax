<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\PublicSurface;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * AvaX framework-level HTTP response abstraction marker.
 *
 * This interface extends PSR-7 ResponseInterface to provide a stable
 * framework-level type that internal flows and capabilities reference.
 *
 * It currently adds no additional methods - its purpose is to decouple
 * internal AvaX code from direct PSR-7 dependency at the boundary.
 */
interface ResponseInterface extends PsrResponseInterface {}
