<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\WarmApplication;

/**
 * AllowedWarmState — Categories of state that are safe to keep between requests in a warm worker.
 *
 * These items are immutable or compiled once and reused across requests.
 * They must never contain per-request runtime values.
 */
enum AllowedWarmState: string
{
    case CompiledContainerDefinitions = 'compiled_container_definitions';
    case StatelessSingletons = 'stateless_singletons';
    case CompiledRouteTable = 'compiled_route_table';
    case ImmutableConfiguration = 'immutable_configuration';
    case ImmutableMetadata = 'immutable_metadata';
    case RouteMetadata = 'route_metadata';
    case DataTransferClassShapeMetadata = 'data_transfer_class_shape_metadata';
    case AttributeMetadata = 'attribute_metadata';
    case CachedSchemaMetadata = 'cached_schema_metadata';
    case LoggerInstancesWithoutRequestContext = 'logger_instances_without_request_context';
    case ConnectionPoolObjects = 'connection_pool_objects';
    case MiddlewarePipelineDefinitions = 'middleware_pipeline_definitions';
    case FeatureFlagDefinitions = 'feature_flag_definitions';
    case PolicyDefinitions = 'policy_definitions';
}
