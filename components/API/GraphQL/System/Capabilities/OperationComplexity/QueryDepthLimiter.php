<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\OperationComplexity;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLSelection;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\ParsedGraphQLOperation;

final readonly class QueryDepthLimiter
{
    public function depth(ParsedGraphQLOperation $operation) : int
    {
        return $this->selectionDepth(selections: $operation->selections, currentDepth: 0);
    }

    /**
     * @param list<GraphQLSelection> $selections
     */
    private function selectionDepth(array $selections, int $currentDepth) : int
    {
        $depth = $currentDepth;

        foreach ($selections as $selection) {
            $depth = max(
                $depth,
                $this->selectionDepth(
                    selections  : $selection->selections,
                    currentDepth: $currentDepth + 1,
                ),
            );
        }

        return $depth;
    }
}
