<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Foundation;

/**
 * The source from which a listener registration originated.
 */
enum ListenerSource: string
{
    case Dsl = 'dsl';
    case Attribute = 'attribute';
    case Configuration = 'configuration';
}
