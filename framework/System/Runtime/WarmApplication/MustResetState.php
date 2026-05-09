<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\WarmApplication;

/**
 * MustResetState — Categories of state that must be reset after every request in a warm worker.
 *
 * Any of these remaining after a request completes indicates a state leak.
 */
enum MustResetState: string
{
    case CurrentRequest = 'current_request';
    case CurrentResponse = 'current_response';
    case CurrentUserAuthContext = 'current_user_auth_context';
    case CurrentSessionContext = 'current_session_context';
    case CorrelationId = 'correlation_id';
    case RequestId = 'request_id';
    case TraceContext = 'trace_context';
    case ScopedContainerInstances = 'scoped_container_instances';
    case RequestScopedCache = 'request_scoped_cache';
    case ValidationErrorContext = 'validation_error_context';
    case MiddlewareRuntimeContext = 'middleware_runtime_context';
    case TemporaryRuntimeState = 'temporary_runtime_state';
    case PerRequestContainerScope = 'per_request_container_scope';
    case QueryBuilderBoundParameters = 'query_builder_bound_parameters';
    case DatabaseTransactionState = 'database_transaction_state';
}
