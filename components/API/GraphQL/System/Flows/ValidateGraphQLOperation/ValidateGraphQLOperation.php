<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Flows\ValidateGraphQLOperation;

use Avax\Components\API\GraphQL\System\Capabilities\GraphQLAuthorization\AuthorizeGraphQLOperation;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLObjectType;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLSelection;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\ParseGraphQLOperation;
use Avax\Components\API\GraphQL\System\Capabilities\OperationComplexity\QueryComplexityAnalyzer;
use Avax\Components\API\GraphQL\System\Capabilities\OperationComplexity\QueryDepthLimiter;
use Avax\Components\API\GraphQL\System\Configuration\GraphQLConfiguration;
use Avax\Components\API\GraphQL\System\Foundation\Failure\GraphQLOperationInvalid;
use Avax\Components\API\GraphQL\System\PublicSurface\GraphQLOperationReport;
use Avax\Components\API\GraphQL\System\PublicSurface\GraphQLSchema;

final readonly class ValidateGraphQLOperation
{
    public function __construct(
        private ParseGraphQLOperation     $parseGraphQLOperation = new ParseGraphQLOperation(),
        private QueryDepthLimiter         $queryDepthLimiter = new QueryDepthLimiter(),
        private QueryComplexityAnalyzer   $queryComplexityAnalyzer = new QueryComplexityAnalyzer(),
        private AuthorizeGraphQLOperation $authorizeGraphQLOperation = new AuthorizeGraphQLOperation(),
    ) {}

    /**
     * @param list<string> $permissions
     */
    public function validate(
        GraphQLSchema        $schema,
        string               $operation,
        GraphQLConfiguration $configuration,
        array                $permissions = [],
    ) : GraphQLOperationReport
    {
        try {
            $parsedOperation = $this->parseGraphQLOperation->parse(source: $operation);
        } catch (GraphQLOperationInvalid $exception) {
            return new GraphQLOperationReport(
                errors    : [$exception->getMessage()],
                depth     : 0,
                complexity: 0,
            );
        }

        $errors        = [];
        $missingFields = [];
        $depth         = $this->queryDepthLimiter->depth(operation: $parsedOperation);
        $complexity    = $this->queryComplexityAnalyzer->complexity(schema: $schema, operation: $parsedOperation);

        if ($depth > $configuration->maxDepth) {
            $errors[] = sprintf(
                'GraphQL operation depth %d exceeds max depth %d.',
                $depth,
                $configuration->maxDepth,
            );
        }

        if ($complexity > $configuration->maxComplexity) {
            $errors[] = sprintf(
                'GraphQL operation complexity %d exceeds max complexity %d.',
                $complexity,
                $configuration->maxComplexity,
            );
        }

        $this->validateSelections(
            schema       : $schema,
            type         : $schema->rootTypeForOperation($parsedOperation->type),
            selections   : $parsedOperation->selections,
            path         : ucfirst($parsedOperation->type),
            errors       : $errors,
            missingFields: $missingFields,
        );

        $unauthorizedFields = $this->authorizeGraphQLOperation->unauthorizedFields(
            schema     : $schema,
            operation  : $parsedOperation,
            permissions: $permissions,
        );

        foreach ($unauthorizedFields as $unauthorizedField) {
            $errors[] = sprintf('GraphQL field %s is not authorized.', $unauthorizedField);
        }

        return new GraphQLOperationReport(
            errors            : $errors,
            depth             : $depth,
            complexity        : $complexity,
            missingFields     : $missingFields,
            unauthorizedFields: $unauthorizedFields,
        );
    }

    /**
     * @param list<GraphQLSelection> $selections
     * @param list<string>           $errors
     * @param list<string>           $missingFields
     */
    private function validateSelections(
        GraphQLSchema     $schema,
        GraphQLObjectType $type,
        array             $selections,
        string            $path,
        array             &$errors,
        array             &$missingFields,
    ) : void
    {
        foreach ($selections as $selection) {
            $field     = $type->findField($selection->name);
            $fieldPath = sprintf('%s.%s', $path, $selection->name);

            if ($field === null) {
                $missingFields[] = $fieldPath;
                $errors[]        = sprintf('GraphQL field %s is not defined.', $fieldPath);

                continue;
            }

            foreach ($field->requiredArguments() as $requiredArgument) {
                if (! array_key_exists($requiredArgument, $selection->arguments)) {
                    $errors[] = sprintf(
                        'GraphQL field %s requires argument %s.',
                        $fieldPath,
                        $requiredArgument,
                    );
                }
            }

            $childType = $schema->findType($field->namedType());

            if ($childType === null && $selection->hasSelections()) {
                $errors[] = sprintf('GraphQL scalar field %s must not select subfields.', $fieldPath);
            }

            if ($childType !== null && ! $selection->hasSelections()) {
                $errors[] = sprintf(
                    'GraphQL object field %s must select subfields for type %s.',
                    $fieldPath,
                    $field->namedType(),
                );
            }

            if ($childType !== null && $selection->hasSelections()) {
                $this->validateSelections(
                    schema       : $schema,
                    type         : $childType,
                    selections   : $selection->selections,
                    path         : $fieldPath,
                    errors       : $errors,
                    missingFields: $missingFields,
                );
            }
        }
    }
}
