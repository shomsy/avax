<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\ResolverExecution;

use Avax\Components\API\GraphQL\System\Capabilities\BatchFieldLoading\DataLoader;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLField;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLObjectType;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\GraphQLSelection;
use Avax\Components\API\GraphQL\System\Capabilities\GraphQLSchemaModel\ParseGraphQLOperation;
use Avax\Components\API\GraphQL\System\Capabilities\ResolverTiming\GraphQLResolverTimeline;
use Avax\Components\API\GraphQL\System\Capabilities\ResolverTiming\RecordResolverTiming;
use Avax\Components\API\GraphQL\System\Configuration\GraphQLConfiguration;
use Avax\Components\API\GraphQL\System\Flows\ValidateGraphQLOperation\ValidateGraphQLOperation;
use Avax\Components\API\GraphQL\System\PublicSurface\GraphQLExecutionResult;
use Avax\Components\API\GraphQL\System\PublicSurface\GraphQLSchema;
use Throwable;

final readonly class ResolveGraphQLOperation
{
    public function __construct(
        private ParseGraphQLOperation    $parseGraphQLOperation = new ParseGraphQLOperation(),
        private ValidateGraphQLOperation $validateGraphQLOperation = new ValidateGraphQLOperation(),
        private RecordResolverTiming     $recordResolverTiming = new RecordResolverTiming(),
    ) {}

    /**
     * @param array<string, mixed> $variables
     * @param list<string>         $permissions
     */
    public function resolve(
        GraphQLSchema           $schema,
        string                  $operation,
        string                  $expectedOperationType,
        GraphQLConfiguration    $configuration,
        GraphQLResolverRegistry $resolvers,
        DataLoader              $dataLoader,
        GraphQLResolverTimeline $timeline,
        array                   $variables = [],
        array                   $permissions = [],
    ) : GraphQLExecutionResult
    {
        $report = $this->validateGraphQLOperation->validate(
            schema       : $schema,
            operation    : $operation,
            configuration: $configuration,
            permissions  : $permissions,
        );

        if (! $report->isValid()) {
            return new GraphQLExecutionResult(
                data    : [],
                errors  : $report->errors,
                timeline: $timeline,
            );
        }

        $parsedOperation = $this->parseGraphQLOperation->parse(source: $operation);

        if ($parsedOperation->type !== $expectedOperationType) {
            return new GraphQLExecutionResult(
                data    : [],
                errors  : [
                              sprintf(
                                  'GraphQL executor expected %s operation, got %s.',
                                  $expectedOperationType,
                                  $parsedOperation->type,
                              ),
                          ],
                timeline: $timeline,
            );
        }

        $errors  = [];
        $context = new GraphQLResolverContext(
            variables  : $variables,
            permissions: $permissions,
            dataLoader : $dataLoader,
            path       : [ucfirst($parsedOperation->type)],
        );

        $data = $this->resolveSelections(
            schema       : $schema,
            type         : $schema->rootTypeForOperation($parsedOperation->type),
            parent       : null,
            selections   : $parsedOperation->selections,
            context      : $context,
            resolvers    : $resolvers,
            timeline     : $timeline,
            configuration: $configuration,
            errors       : $errors,
        );

        return new GraphQLExecutionResult(
            data    : $data,
            errors  : $errors,
            timeline: $timeline,
        );
    }

    /**
     * @param list<GraphQLSelection> $selections
     * @param list<string>           $errors
     *
     * @return array<string, mixed>
     */
    private function resolveSelections(
        GraphQLSchema           $schema,
        GraphQLObjectType       $type,
        mixed                   $parent,
        array                   $selections,
        GraphQLResolverContext  $context,
        GraphQLResolverRegistry $resolvers,
        GraphQLResolverTimeline $timeline,
        GraphQLConfiguration    $configuration,
        array                   &$errors,
    ) : array
    {
        $data = [];

        foreach ($selections as $selection) {
            $field = $type->findField($selection->name);

            if ($field === null) {
                continue;
            }

            $path  = [...$context->path, $selection->dataKey()];
            $value = $this->resolveField(
                type         : $type,
                field        : $field,
                parent       : $parent,
                selection    : $selection,
                context      : $context->withPath(path: $path),
                resolvers    : $resolvers,
                timeline     : $timeline,
                configuration: $configuration,
                errors       : $errors,
            );

            $childType = $schema->findType($field->namedType());

            if ($childType !== null && $selection->hasSelections()) {
                $value = $field->isList()
                    ? $this->resolveListSelection(
                        schema       : $schema,
                        type         : $childType,
                        values       : is_array($value) ? $value : [],
                        selections   : $selection->selections,
                        context      : $context->withPath(path: $path),
                        resolvers    : $resolvers,
                        timeline     : $timeline,
                        configuration: $configuration,
                        errors       : $errors,
                    )
                    : $this->resolveSelections(
                        schema       : $schema,
                        type         : $childType,
                        parent       : $value,
                        selections   : $selection->selections,
                        context      : $context->withPath(path: $path),
                        resolvers    : $resolvers,
                        timeline     : $timeline,
                        configuration: $configuration,
                        errors       : $errors,
                    );
            }

            $data[$selection->dataKey()] = $value;
        }

        return $data;
    }

    /**
     * @param list<string> $errors
     */
    private function resolveField(
        GraphQLObjectType       $type,
        GraphQLField            $field,
        mixed                   $parent,
        GraphQLSelection        $selection,
        GraphQLResolverContext  $context,
        GraphQLResolverRegistry $resolvers,
        GraphQLResolverTimeline $timeline,
        GraphQLConfiguration    $configuration,
        array                   &$errors,
    ) : mixed
    {
        $resolver = $resolvers->resolverFor(typeName: $type->name, fieldName: $field->name);

        if ($resolver === null && $parent === null) {
            $errors[] = sprintf('GraphQL resolver %s.%s is not registered.', $type->name, $field->name);

            return null;
        }

        if ($resolver === null) {
            return $this->readValue(parent: $parent, key: $field->name);
        }

        try {
            return $this->recordResolverTiming->record(
                timeline : $timeline,
                typeName : $type->name,
                fieldName: $field->name,
                path     : $context->path,
                resolver : fn () : mixed => $resolver(
                    $parent,
                    $context->argumentsFor(selection: $selection),
                    $context,
                ),
                enabled  : $configuration->recordResolverTiming,
            );
        } catch (Throwable $exception) {
            $errors[] = sprintf(
                'GraphQL resolver %s.%s failed: %s',
                $type->name,
                $field->name,
                $exception->getMessage(),
            );

            return null;
        }
    }

    private function readValue(mixed $parent, string $key) : mixed
    {
        if (is_array($parent) && array_key_exists($key, $parent)) {
            return $parent[$key];
        }

        if (is_object($parent) && isset($parent->{$key})) {
            return $parent->{$key};
        }

        return null;
    }

    /**
     * @param array<int|string, mixed> $values
     * @param list<GraphQLSelection>   $selections
     * @param list<string>             $errors
     *
     * @return list<array<string, mixed>>
     */
    private function resolveListSelection(
        GraphQLSchema           $schema,
        GraphQLObjectType       $type,
        array                   $values,
        array                   $selections,
        GraphQLResolverContext  $context,
        GraphQLResolverRegistry $resolvers,
        GraphQLResolverTimeline $timeline,
        GraphQLConfiguration    $configuration,
        array                   &$errors,
    ) : array
    {
        $resolved = [];

        foreach ($values as $index => $value) {
            $resolved[] = $this->resolveSelections(
                schema       : $schema,
                type         : $type,
                parent       : $value,
                selections   : $selections,
                context      : $context->withPath(path: [...$context->path, (string) $index]),
                resolvers    : $resolvers,
                timeline     : $timeline,
                configuration: $configuration,
                errors       : $errors,
            );
        }

        return $resolved;
    }
}
