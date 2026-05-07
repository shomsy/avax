<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Capabilities\Compatibility;

enum CompatibilityChangeType: string
{
    case REMOVED_ENDPOINT         = 'removed_endpoint';
    case REMOVED_METHOD           = 'removed_method';
    case REMOVED_PATH_PARAM       = 'removed_path_param';
    case REMOVED_QUERY_PARAM      = 'removed_query_param';
    case REMOVED_HEADER           = 'removed_header';
    case REMOVED_PROPERTY         = 'removed_property';
    case REMOVED_AUTH_REQUIREMENT = 'removed_auth_requirement';
    case CHANGED_STATUS_CODE      = 'changed_status_code';
    case CHANGED_PROPERTY_TYPE    = 'changed_property_type';
    case CHANGED_PROPERTY_FORMAT  = 'changed_property_format';
    case ADDED_REQUIRED_FIELD     = 'added_required_field';
    case REMOVED_SUCCESS_RESPONSE = 'removed_success_response';
    case ADDED_ERROR_RESPONSE     = 'added_error_response';
}
