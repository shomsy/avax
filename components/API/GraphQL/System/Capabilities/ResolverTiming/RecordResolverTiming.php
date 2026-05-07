<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\ResolverTiming;

use Closure;

final readonly class RecordResolverTiming
{
    /**
     * @param list<string> $path
     */
    public function record(
        GraphQLResolverTimeline $timeline,
        string                  $typeName,
        string                  $fieldName,
        array                   $path,
        Closure                 $resolver,
        bool                    $enabled,
    ) : mixed
    {
        if (! $enabled) {
            return $resolver();
        }

        $startedAt = hrtime(true);

        try {
            return $resolver();
        } finally {
            $timeline->record(new GraphQLResolverTiming(
                                  typeName           : $typeName,
                                  fieldName          : $fieldName,
                                  path               : $path,
                                  durationNanoseconds: hrtime(true) - $startedAt,
                              ));
        }
    }
}
