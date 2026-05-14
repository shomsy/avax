<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\Flows\SelectBestMediaType;

final readonly class SelectBestMediaType
{
    /**
     * @param list<string> $offerings
     */
    public function select(string $accept, array $offerings) : string|null
    {
        $types = array_map('trim', explode(',', $accept));

        foreach ($types as $type) {
            foreach ($offerings as $offering) {
                if ($type === $offering || str_contains($type, '*')) {
                    return $offering;
                }
            }
        }

        return null;
    }
}
