<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\PublicSurface;

final readonly class GraphQLOperationReport
{
    /**
     * @param list<string> $errors
     * @param list<string> $missingFields
     * @param list<string> $unauthorizedFields
     */
    public function __construct(
        public array $errors,
        public int   $depth,
        public int   $complexity,
        public array $missingFields = [],
        public array $unauthorizedFields = [],
    ) {}

    public function isValid() : bool
    {
        return $this->errors === [];
    }
}
