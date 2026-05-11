<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Capabilities\Visibility;

final readonly class ResolveObjectVisibility
{
    public function execute(string|null $visibility) : ObjectVisibility
    {
        if ($visibility === null) {
            return ObjectVisibility::PRIVATE;
        }

        return match (strtolower($visibility)) {
            'public', 'public-read' => ObjectVisibility::PUBLIC,
            default                 => ObjectVisibility::PRIVATE,
        };
    }
}