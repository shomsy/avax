<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\ORM\Relations;

enum RelationKind: string
{
    case ManyToOne  = 'many_to_one';
    case OneToMany  = 'one_to_many';
    case OneToOne   = 'one_to_one';
    case ManyToMany = 'many_to_many';
}
