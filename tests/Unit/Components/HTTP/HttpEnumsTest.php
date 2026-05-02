<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP;

use Avax\Components\HTTP\System\Foundation\Values\ContentType;
use Avax\Components\HTTP\System\Foundation\Values\HeaderName;
use Avax\Components\HTTP\System\Foundation\Values\HttpMethod;
use Avax\Components\HTTP\System\Foundation\Values\HttpReasonPhrase;
use Avax\Components\HTTP\System\Foundation\Values\HttpStatusCode;
use Avax\Components\HTTP\System\Foundation\Values\RequestOption;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * Comprehensive unit tests for HTTP Enums and Value Objects.
 *
 * Tests HttpMethod, HttpStatusCode, HttpReasonPhrase, ContentType,
 * HeaderName, and RequestOption enums/value objects.
 */
final class HttpEnumsTest extends TestCase
{
    // =========================================================================
    // 1. HttpMethod Enum Tests
    // =========================================================================

    public function test_http_method_all_nine_cases_exist() : void
    {
        $cases = HttpMethod::cases();
        $this->assertCount(9, $cases);
    }

    public function test_http_method_get_case() : void
    {
        $this->assertSame('GET', HttpMethod::GET->value);
    }

    public function test_http_method_post_case() : void
    {
        $this->assertSame('POST', HttpMethod::POST->value);
    }

    public function test_http_method_put_case() : void
    {
        $this->assertSame('PUT', HttpMethod::PUT->value);
    }

    public function test_http_method_patch_case() : void
    {
        $this->assertSame('PATCH', HttpMethod::PATCH->value);
    }

    public function test_http_method_delete_case() : void
    {
        $this->assertSame('DELETE', HttpMethod::DELETE->value);
    }

    public function test_http_method_head_case() : void
    {
        $this->assertSame('HEAD', HttpMethod::HEAD->value);
    }

    public function test_http_method_options_case() : void
    {
        $this->assertSame('OPTIONS', HttpMethod::OPTIONS->value);
    }

    public function test_http_method_connect_case() : void
    {
        $this->assertSame('CONNECT', HttpMethod::CONNECT->value);
    }

    public function test_http_method_trace_case() : void
    {
        $this->assertSame('TRACE', HttpMethod::TRACE->value);
    }

    public function test_http_method_is_safe_get() : void
    {
        $this->assertTrue(HttpMethod::GET->isSafe());
    }

    public function test_http_method_is_safe_head() : void
    {
        $this->assertTrue(HttpMethod::HEAD->isSafe());
    }

    public function test_http_method_is_safe_options() : void
    {
        $this->assertTrue(HttpMethod::OPTIONS->isSafe());
    }

    public function test_http_method_is_safe_trace() : void
    {
        $this->assertTrue(HttpMethod::TRACE->isSafe());
    }

    public function test_http_method_is_not_safe_post() : void
    {
        $this->assertFalse(HttpMethod::POST->isSafe());
    }

    public function test_http_method_is_not_safe_put() : void
    {
        $this->assertFalse(HttpMethod::PUT->isSafe());
    }

    public function test_http_method_is_not_safe_patch() : void
    {
        $this->assertFalse(HttpMethod::PATCH->isSafe());
    }

    public function test_http_method_is_not_safe_delete() : void
    {
        $this->assertFalse(HttpMethod::DELETE->isSafe());
    }

    public function test_http_method_is_not_safe_connect() : void
    {
        $this->assertFalse(HttpMethod::CONNECT->isSafe());
    }

    public function test_http_method_is_idempotent_get() : void
    {
        $this->assertTrue(HttpMethod::GET->isIdempotent());
    }

    public function test_http_method_is_idempotent_head() : void
    {
        $this->assertTrue(HttpMethod::HEAD->isIdempotent());
    }

    public function test_http_method_is_idempotent_put() : void
    {
        $this->assertTrue(HttpMethod::PUT->isIdempotent());
    }

    public function test_http_method_is_idempotent_delete() : void
    {
        $this->assertTrue(HttpMethod::DELETE->isIdempotent());
    }

    public function test_http_method_is_idempotent_options() : void
    {
        $this->assertTrue(HttpMethod::OPTIONS->isIdempotent());
    }

    public function test_http_method_is_idempotent_trace() : void
    {
        $this->assertTrue(HttpMethod::TRACE->isIdempotent());
    }

    public function test_http_method_is_not_idempotent_post() : void
    {
        $this->assertFalse(HttpMethod::POST->isIdempotent());
    }

    public function test_http_method_is_not_idempotent_patch() : void
    {
        $this->assertFalse(HttpMethod::PATCH->isIdempotent());
    }

    public function test_http_method_is_not_idempotent_connect() : void
    {
        $this->assertFalse(HttpMethod::CONNECT->isIdempotent());
    }

    public function test_http_method_allows_body_post() : void
    {
        $this->assertTrue(HttpMethod::POST->allowsBody());
    }

    public function test_http_method_allows_body_put() : void
    {
        $this->assertTrue(HttpMethod::PUT->allowsBody());
    }

    public function test_http_method_allows_body_patch() : void
    {
        $this->assertTrue(HttpMethod::PATCH->allowsBody());
    }

    public function test_http_method_allows_body_delete() : void
    {
        $this->assertTrue(HttpMethod::DELETE->allowsBody());
    }

    public function test_http_method_does_not_allow_body_get() : void
    {
        $this->assertFalse(HttpMethod::GET->allowsBody());
    }

    public function test_http_method_does_not_allow_body_head() : void
    {
        $this->assertFalse(HttpMethod::HEAD->allowsBody());
    }

    public function test_http_method_does_not_allow_body_options() : void
    {
        $this->assertFalse(HttpMethod::OPTIONS->allowsBody());
    }

    public function test_http_method_does_not_allow_body_connect() : void
    {
        $this->assertFalse(HttpMethod::CONNECT->allowsBody());
    }

    public function test_http_method_does_not_allow_body_trace() : void
    {
        $this->assertFalse(HttpMethod::TRACE->allowsBody());
    }

    public function test_http_method_from_name_valid_uppercase() : void
    {
        $this->assertSame(HttpMethod::GET, HttpMethod::fromName('GET'));
    }

    public function test_http_method_from_name_valid_lowercase() : void
    {
        $this->assertSame(HttpMethod::POST, HttpMethod::fromName('post'));
    }

    public function test_http_method_from_name_valid_mixed_case() : void
    {
        $this->assertSame(HttpMethod::DELETE, HttpMethod::fromName('DeLeTe'));
    }

    public function test_http_method_from_name_invalid_throws_value_error() : void
    {
        $this->expectException(ValueError::class);
        HttpMethod::fromName('INVALID');
    }

    public function test_http_method_try_from_name_valid() : void
    {
        $this->assertSame(HttpMethod::PUT, HttpMethod::tryFromName('put'));
    }

    public function test_http_method_try_from_name_invalid_returns_null() : void
    {
        $this->assertNull(HttpMethod::tryFromName('INVALID'));
    }

    public function test_http_method_is_valid_true() : void
    {
        $this->assertTrue(HttpMethod::isValid('GET'));
        $this->assertTrue(HttpMethod::isValid('post'));
        $this->assertTrue(HttpMethod::isValid('Patch'));
    }

    public function test_http_method_is_valid_false() : void
    {
        $this->assertFalse(HttpMethod::isValid('INVALID'));
        $this->assertFalse(HttpMethod::isValid(''));
    }

    // =========================================================================
    // 2. HttpStatusCode Enum Tests
    // =========================================================================

    public function test_http_status_code_ok() : void
    {
        $this->assertSame(200, HttpStatusCode::OK->value);
    }

    public function test_http_status_code_created() : void
    {
        $this->assertSame(201, HttpStatusCode::CREATED->value);
    }

    public function test_http_status_code_accepted() : void
    {
        $this->assertSame(202, HttpStatusCode::ACCEPTED->value);
    }

    public function test_http_status_code_no_content() : void
    {
        $this->assertSame(204, HttpStatusCode::NO_CONTENT->value);
    }

    public function test_http_status_code_moved_permanently() : void
    {
        $this->assertSame(301, HttpStatusCode::MOVED_PERMANENTLY->value);
    }

    public function test_http_status_code_found() : void
    {
        $this->assertSame(302, HttpStatusCode::FOUND->value);
    }

    public function test_http_status_code_see_other() : void
    {
        $this->assertSame(303, HttpStatusCode::SEE_OTHER->value);
    }

    public function test_http_status_code_not_modified() : void
    {
        $this->assertSame(304, HttpStatusCode::NOT_MODIFIED->value);
    }

    public function test_http_status_code_temporary_redirect() : void
    {
        $this->assertSame(307, HttpStatusCode::TEMPORARY_REDIRECT->value);
    }

    public function test_http_status_code_permanent_redirect() : void
    {
        $this->assertSame(308, HttpStatusCode::PERMANENT_REDIRECT->value);
    }

    public function test_http_status_code_bad_request() : void
    {
        $this->assertSame(400, HttpStatusCode::BAD_REQUEST->value);
    }

    public function test_http_status_code_unauthorized() : void
    {
        $this->assertSame(401, HttpStatusCode::UNAUTHORIZED->value);
    }

    public function test_http_status_code_forbidden() : void
    {
        $this->assertSame(403, HttpStatusCode::FORBIDDEN->value);
    }

    public function test_http_status_code_not_found() : void
    {
        $this->assertSame(404, HttpStatusCode::NOT_FOUND->value);
    }

    public function test_http_status_code_method_not_allowed() : void
    {
        $this->assertSame(405, HttpStatusCode::METHOD_NOT_ALLOWED->value);
    }

    public function test_http_status_code_not_acceptable() : void
    {
        $this->assertSame(406, HttpStatusCode::NOT_ACCEPTABLE->value);
    }

    public function test_http_status_code_conflict() : void
    {
        $this->assertSame(409, HttpStatusCode::CONFLICT->value);
    }

    public function test_http_status_code_gone() : void
    {
        $this->assertSame(410, HttpStatusCode::GONE->value);
    }

    public function test_http_status_code_length_required() : void
    {
        $this->assertSame(411, HttpStatusCode::LENGTH_REQUIRED->value);
    }

    public function test_http_status_code_precondition_failed() : void
    {
        $this->assertSame(412, HttpStatusCode::PRECONDITION_FAILED->value);
    }

    public function test_http_status_code_payload_too_large() : void
    {
        $this->assertSame(413, HttpStatusCode::PAYLOAD_TOO_LARGE->value);
    }

    public function test_http_status_code_uri_too_long() : void
    {
        $this->assertSame(414, HttpStatusCode::URI_TOO_LONG->value);
    }

    public function test_http_status_code_unsupported_media_type() : void
    {
        $this->assertSame(415, HttpStatusCode::UNSUPPORTED_MEDIA_TYPE->value);
    }

    public function test_http_status_code_range_not_satisfiable() : void
    {
        $this->assertSame(416, HttpStatusCode::RANGE_NOT_SATISFIABLE->value);
    }

    public function test_http_status_code_expectation_failed() : void
    {
        $this->assertSame(417, HttpStatusCode::EXPECTATION_FAILED->value);
    }

    public function test_http_status_code_unprocessable_entity() : void
    {
        $this->assertSame(422, HttpStatusCode::UNPROCESSABLE_ENTITY->value);
    }

    public function test_http_status_code_locked() : void
    {
        $this->assertSame(423, HttpStatusCode::LOCKED->value);
    }

    public function test_http_status_code_failed_dependency() : void
    {
        $this->assertSame(424, HttpStatusCode::FAILED_DEPENDENCY->value);
    }

    public function test_http_status_code_too_early() : void
    {
        $this->assertSame(425, HttpStatusCode::TOO_EARLY->value);
    }

    public function test_http_status_code_upgrade_required() : void
    {
        $this->assertSame(426, HttpStatusCode::UPGRADE_REQUIRED->value);
    }

    public function test_http_status_code_precondition_required() : void
    {
        $this->assertSame(428, HttpStatusCode::PRECONDITION_REQUIRED->value);
    }

    public function test_http_status_code_too_many_requests() : void
    {
        $this->assertSame(429, HttpStatusCode::TOO_MANY_REQUESTS->value);
    }

    public function test_http_status_code_internal_server_error() : void
    {
        $this->assertSame(500, HttpStatusCode::INTERNAL_SERVER_ERROR->value);
    }

    public function test_http_status_code_not_implemented() : void
    {
        $this->assertSame(501, HttpStatusCode::NOT_IMPLEMENTED->value);
    }

    public function test_http_status_code_bad_gateway() : void
    {
        $this->assertSame(502, HttpStatusCode::BAD_GATEWAY->value);
    }

    public function test_http_status_code_service_unavailable() : void
    {
        $this->assertSame(503, HttpStatusCode::SERVICE_UNAVAILABLE->value);
    }

    public function test_http_status_code_gateway_timeout() : void
    {
        $this->assertSame(504, HttpStatusCode::GATEWAY_TIMEOUT->value);
    }

    public function test_http_status_code_http_version_not_supported() : void
    {
        $this->assertSame(505, HttpStatusCode::HTTP_VERSION_NOT_SUPPORTED->value);
    }

    public function test_http_status_code_insufficient_storage() : void
    {
        $this->assertSame(507, HttpStatusCode::INSUFFICIENT_STORAGE->value);
    }

    public function test_http_status_code_loop_detected() : void
    {
        $this->assertSame(508, HttpStatusCode::LOOP_DETECTED->value);
    }

    public function test_http_status_code_not_extended() : void
    {
        $this->assertSame(510, HttpStatusCode::NOT_EXTENDED->value);
    }

    public function test_http_status_code_network_authentication_required() : void
    {
        $this->assertSame(511, HttpStatusCode::NETWORK_AUTHENTICATION_REQUIRED->value);
    }

    public function test_http_status_code_count_at_least_17() : void
    {
        $count = count(HttpStatusCode::cases());
        $this->assertGreaterThanOrEqual(17, $count, 'Expected at least 17 status codes');
    }

    public function test_http_status_code_is_ok_true_for_200() : void
    {
        $this->assertTrue(HttpStatusCode::OK->isOk());
    }

    public function test_http_status_code_is_ok_false_for_others() : void
    {
        $this->assertFalse(HttpStatusCode::CREATED->isOk());
        $this->assertFalse(HttpStatusCode::NOT_FOUND->isOk());
        $this->assertFalse(HttpStatusCode::INTERNAL_SERVER_ERROR->isOk());
    }

    public function test_http_status_code_is_informational_none_in_enum() : void
    {
        // HttpStatusCode enum does not include 1xx codes
        foreach (HttpStatusCode::cases() as $code) {
            $this->assertFalse($code->isInformational(), "Status code {$code->value} should not be informational");
        }
    }

    public function test_http_status_code_is_success_2xx() : void
    {
        $this->assertTrue(HttpStatusCode::OK->isSuccess());
        $this->assertTrue(HttpStatusCode::CREATED->isSuccess());
        $this->assertTrue(HttpStatusCode::ACCEPTED->isSuccess());
        $this->assertTrue(HttpStatusCode::NO_CONTENT->isSuccess());
    }

    public function test_http_status_code_is_success_false_for_non_2xx() : void
    {
        $this->assertFalse(HttpStatusCode::MOVED_PERMANENTLY->isSuccess());
        $this->assertFalse(HttpStatusCode::NOT_FOUND->isSuccess());
        $this->assertFalse(HttpStatusCode::INTERNAL_SERVER_ERROR->isSuccess());
    }

    public function test_http_status_code_is_redirect_3xx() : void
    {
        $this->assertTrue(HttpStatusCode::MOVED_PERMANENTLY->isRedirect());
        $this->assertTrue(HttpStatusCode::FOUND->isRedirect());
        $this->assertTrue(HttpStatusCode::SEE_OTHER->isRedirect());
        $this->assertTrue(HttpStatusCode::NOT_MODIFIED->isRedirect());
        $this->assertTrue(HttpStatusCode::TEMPORARY_REDIRECT->isRedirect());
        $this->assertTrue(HttpStatusCode::PERMANENT_REDIRECT->isRedirect());
    }

    public function test_http_status_code_is_redirect_false_for_non_3xx() : void
    {
        $this->assertFalse(HttpStatusCode::OK->isRedirect());
        $this->assertFalse(HttpStatusCode::NOT_FOUND->isRedirect());
        $this->assertFalse(HttpStatusCode::INTERNAL_SERVER_ERROR->isRedirect());
    }

    public function test_http_status_code_is_client_error_4xx() : void
    {
        $this->assertTrue(HttpStatusCode::BAD_REQUEST->isClientError());
        $this->assertTrue(HttpStatusCode::UNAUTHORIZED->isClientError());
        $this->assertTrue(HttpStatusCode::FORBIDDEN->isClientError());
        $this->assertTrue(HttpStatusCode::NOT_FOUND->isClientError());
        $this->assertTrue(HttpStatusCode::UNPROCESSABLE_ENTITY->isClientError());
        $this->assertTrue(HttpStatusCode::TOO_MANY_REQUESTS->isClientError());
    }

    public function test_http_status_code_is_client_error_false_for_non_4xx() : void
    {
        $this->assertFalse(HttpStatusCode::OK->isClientError());
        $this->assertFalse(HttpStatusCode::MOVED_PERMANENTLY->isClientError());
        $this->assertFalse(HttpStatusCode::INTERNAL_SERVER_ERROR->isClientError());
    }

    public function test_http_status_code_is_server_error_5xx() : void
    {
        $this->assertTrue(HttpStatusCode::INTERNAL_SERVER_ERROR->isServerError());
        $this->assertTrue(HttpStatusCode::NOT_IMPLEMENTED->isServerError());
        $this->assertTrue(HttpStatusCode::BAD_GATEWAY->isServerError());
        $this->assertTrue(HttpStatusCode::SERVICE_UNAVAILABLE->isServerError());
        $this->assertTrue(HttpStatusCode::GATEWAY_TIMEOUT->isServerError());
        $this->assertTrue(HttpStatusCode::HTTP_VERSION_NOT_SUPPORTED->isServerError());
        $this->assertTrue(HttpStatusCode::NETWORK_AUTHENTICATION_REQUIRED->isServerError());
    }

    public function test_http_status_code_is_server_error_false_for_non_5xx() : void
    {
        $this->assertFalse(HttpStatusCode::OK->isServerError());
        $this->assertFalse(HttpStatusCode::NOT_FOUND->isServerError());
    }

    public function test_http_status_code_get_reason_phrase_2xx() : void
    {
        $this->assertSame('OK', HttpStatusCode::OK->getReasonPhrase());
        $this->assertSame('Created', HttpStatusCode::CREATED->getReasonPhrase());
        $this->assertSame('Accepted', HttpStatusCode::ACCEPTED->getReasonPhrase());
        $this->assertSame('No Content', HttpStatusCode::NO_CONTENT->getReasonPhrase());
    }

    public function test_http_status_code_get_reason_phrase_3xx() : void
    {
        $this->assertSame('Moved Permanently', HttpStatusCode::MOVED_PERMANENTLY->getReasonPhrase());
        $this->assertSame('Found', HttpStatusCode::FOUND->getReasonPhrase());
        $this->assertSame('See Other', HttpStatusCode::SEE_OTHER->getReasonPhrase());
        $this->assertSame('Not Modified', HttpStatusCode::NOT_MODIFIED->getReasonPhrase());
        $this->assertSame('Temporary Redirect', HttpStatusCode::TEMPORARY_REDIRECT->getReasonPhrase());
        $this->assertSame('Permanent Redirect', HttpStatusCode::PERMANENT_REDIRECT->getReasonPhrase());
    }

    public function test_http_status_code_get_reason_phrase_4xx() : void
    {
        $this->assertSame('Bad Request', HttpStatusCode::BAD_REQUEST->getReasonPhrase());
        $this->assertSame('Unauthorized', HttpStatusCode::UNAUTHORIZED->getReasonPhrase());
        $this->assertSame('Forbidden', HttpStatusCode::FORBIDDEN->getReasonPhrase());
        $this->assertSame('Not Found', HttpStatusCode::NOT_FOUND->getReasonPhrase());
        $this->assertSame('Method Not Allowed', HttpStatusCode::METHOD_NOT_ALLOWED->getReasonPhrase());
        $this->assertSame('Not Acceptable', HttpStatusCode::NOT_ACCEPTABLE->getReasonPhrase());
        $this->assertSame('Conflict', HttpStatusCode::CONFLICT->getReasonPhrase());
        $this->assertSame('Gone', HttpStatusCode::GONE->getReasonPhrase());
        $this->assertSame('Length Required', HttpStatusCode::LENGTH_REQUIRED->getReasonPhrase());
        $this->assertSame('Precondition Failed', HttpStatusCode::PRECONDITION_FAILED->getReasonPhrase());
        $this->assertSame('Payload Too Large', HttpStatusCode::PAYLOAD_TOO_LARGE->getReasonPhrase());
        $this->assertSame('URI Too Long', HttpStatusCode::URI_TOO_LONG->getReasonPhrase());
        $this->assertSame('Unsupported Media Type', HttpStatusCode::UNSUPPORTED_MEDIA_TYPE->getReasonPhrase());
        $this->assertSame('Range Not Satisfiable', HttpStatusCode::RANGE_NOT_SATISFIABLE->getReasonPhrase());
        $this->assertSame('Expectation Failed', HttpStatusCode::EXPECTATION_FAILED->getReasonPhrase());
        $this->assertSame('Unprocessable Entity', HttpStatusCode::UNPROCESSABLE_ENTITY->getReasonPhrase());
        $this->assertSame('Locked', HttpStatusCode::LOCKED->getReasonPhrase());
        $this->assertSame('Failed Dependency', HttpStatusCode::FAILED_DEPENDENCY->getReasonPhrase());
        $this->assertSame('Too Early', HttpStatusCode::TOO_EARLY->getReasonPhrase());
        $this->assertSame('Upgrade Required', HttpStatusCode::UPGRADE_REQUIRED->getReasonPhrase());
        $this->assertSame('Precondition Required', HttpStatusCode::PRECONDITION_REQUIRED->getReasonPhrase());
        $this->assertSame('Too Many Requests', HttpStatusCode::TOO_MANY_REQUESTS->getReasonPhrase());
    }

    public function test_http_status_code_get_reason_phrase_5xx() : void
    {
        $this->assertSame('Internal Server Error', HttpStatusCode::INTERNAL_SERVER_ERROR->getReasonPhrase());
        $this->assertSame('Not Implemented', HttpStatusCode::NOT_IMPLEMENTED->getReasonPhrase());
        $this->assertSame('Bad Gateway', HttpStatusCode::BAD_GATEWAY->getReasonPhrase());
        $this->assertSame('Service Unavailable', HttpStatusCode::SERVICE_UNAVAILABLE->getReasonPhrase());
        $this->assertSame('Gateway Timeout', HttpStatusCode::GATEWAY_TIMEOUT->getReasonPhrase());
        $this->assertSame('HTTP Version Not Supported', HttpStatusCode::HTTP_VERSION_NOT_SUPPORTED->getReasonPhrase());
        $this->assertSame('Insufficient Storage', HttpStatusCode::INSUFFICIENT_STORAGE->getReasonPhrase());
        $this->assertSame('Loop Detected', HttpStatusCode::LOOP_DETECTED->getReasonPhrase());
        $this->assertSame('Not Extended', HttpStatusCode::NOT_EXTENDED->getReasonPhrase());
        $this->assertSame('Network Authentication Required', HttpStatusCode::NETWORK_AUTHENTICATION_REQUIRED->getReasonPhrase());
    }

    public function test_http_status_code_from_code_valid() : void
    {
        $this->assertSame(HttpStatusCode::OK, HttpStatusCode::fromCode(200));
        $this->assertSame(HttpStatusCode::NOT_FOUND, HttpStatusCode::fromCode(404));
        $this->assertSame(HttpStatusCode::INTERNAL_SERVER_ERROR, HttpStatusCode::fromCode(500));
    }

    public function test_http_status_code_from_code_invalid_throws_value_error() : void
    {
        $this->expectException(ValueError::class);
        HttpStatusCode::fromCode(999);
    }

    public function test_http_status_code_try_from_code_valid() : void
    {
        $this->assertSame(HttpStatusCode::OK, HttpStatusCode::tryFromCode(200));
        $this->assertSame(HttpStatusCode::CREATED, HttpStatusCode::tryFromCode(201));
    }

    public function test_http_status_code_try_from_code_invalid_returns_null() : void
    {
        $this->assertNull(HttpStatusCode::tryFromCode(999));
        $this->assertNull(HttpStatusCode::tryFromCode(0));
        $this->assertNull(HttpStatusCode::tryFromCode(-1));
    }

    // =========================================================================
    // 3. HttpReasonPhrase Value Object Tests
    // =========================================================================

    public function test_http_reason_phrase_from_status_code_200() : void
    {
        $this->assertSame('OK', HttpReasonPhrase::fromStatusCode(200));
    }

    public function test_http_reason_phrase_from_status_code_201() : void
    {
        $this->assertSame('Created', HttpReasonPhrase::fromStatusCode(201));
    }

    public function test_http_reason_phrase_from_status_code_204() : void
    {
        $this->assertSame('No Content', HttpReasonPhrase::fromStatusCode(204));
    }

    public function test_http_reason_phrase_from_status_code_301() : void
    {
        $this->assertSame('Moved Permanently', HttpReasonPhrase::fromStatusCode(301));
    }

    public function test_http_reason_phrase_from_status_code_302() : void
    {
        $this->assertSame('Found', HttpReasonPhrase::fromStatusCode(302));
    }

    public function test_http_reason_phrase_from_status_code_304() : void
    {
        $this->assertSame('Not Modified', HttpReasonPhrase::fromStatusCode(304));
    }

    public function test_http_reason_phrase_from_status_code_400() : void
    {
        $this->assertSame('Bad Request', HttpReasonPhrase::fromStatusCode(400));
    }

    public function test_http_reason_phrase_from_status_code_401() : void
    {
        $this->assertSame('Unauthorized', HttpReasonPhrase::fromStatusCode(401));
    }

    public function test_http_reason_phrase_from_status_code_403() : void
    {
        $this->assertSame('Forbidden', HttpReasonPhrase::fromStatusCode(403));
    }

    public function test_http_reason_phrase_from_status_code_404() : void
    {
        $this->assertSame('Not Found', HttpReasonPhrase::fromStatusCode(404));
    }

    public function test_http_reason_phrase_from_status_code_405() : void
    {
        $this->assertSame('Method Not Allowed', HttpReasonPhrase::fromStatusCode(405));
    }

    public function test_http_reason_phrase_from_status_code_422() : void
    {
        $this->assertSame('Unprocessable Entity', HttpReasonPhrase::fromStatusCode(422));
    }

    public function test_http_reason_phrase_from_status_code_500() : void
    {
        $this->assertSame('Internal Server Error', HttpReasonPhrase::fromStatusCode(500));
    }

    public function test_http_reason_phrase_from_status_code_502() : void
    {
        $this->assertSame('Bad Gateway', HttpReasonPhrase::fromStatusCode(502));
    }

    public function test_http_reason_phrase_from_status_code_503() : void
    {
        $this->assertSame('Service Unavailable', HttpReasonPhrase::fromStatusCode(503));
    }

    public function test_http_reason_phrase_from_status_code_504() : void
    {
        $this->assertSame('Gateway Timeout', HttpReasonPhrase::fromStatusCode(504));
    }

    public function test_http_reason_phrase_from_status_code_unknown() : void
    {
        $this->assertNull(HttpReasonPhrase::fromStatusCode(999));
        $this->assertNull(HttpReasonPhrase::fromStatusCode(0));
    }

    public function test_http_reason_phrase_from_status_code_1xx() : void
    {
        $this->assertSame('Continue', HttpReasonPhrase::fromStatusCode(100));
        $this->assertSame('Switching Protocols', HttpReasonPhrase::fromStatusCode(101));
        $this->assertSame('Processing', HttpReasonPhrase::fromStatusCode(102));
        $this->assertSame('Early Hints', HttpReasonPhrase::fromStatusCode(103));
    }

    public function test_http_reason_phrase_from_status_code_2xx_extended() : void
    {
        $this->assertSame('Non-Authoritative Information', HttpReasonPhrase::fromStatusCode(203));
        $this->assertSame('Reset Content', HttpReasonPhrase::fromStatusCode(205));
        $this->assertSame('Partial Content', HttpReasonPhrase::fromStatusCode(206));
        $this->assertSame('Multi-Status', HttpReasonPhrase::fromStatusCode(207));
        $this->assertSame('Already Reported', HttpReasonPhrase::fromStatusCode(208));
        $this->assertSame('IM Used', HttpReasonPhrase::fromStatusCode(226));
    }

    public function test_http_reason_phrase_from_status_code_3xx_extended() : void
    {
        $this->assertSame('Multiple Choices', HttpReasonPhrase::fromStatusCode(300));
        $this->assertSame('See Other', HttpReasonPhrase::fromStatusCode(303));
        $this->assertSame('Temporary Redirect', HttpReasonPhrase::fromStatusCode(307));
        $this->assertSame('Permanent Redirect', HttpReasonPhrase::fromStatusCode(308));
    }

    public function test_http_reason_phrase_from_status_code_4xx_extended() : void
    {
        $this->assertSame('Payment Required', HttpReasonPhrase::fromStatusCode(402));
        $this->assertSame('Not Acceptable', HttpReasonPhrase::fromStatusCode(406));
        $this->assertSame('Proxy Authentication Required', HttpReasonPhrase::fromStatusCode(407));
        $this->assertSame('Request Timeout', HttpReasonPhrase::fromStatusCode(408));
        $this->assertSame('Conflict', HttpReasonPhrase::fromStatusCode(409));
        $this->assertSame('Gone', HttpReasonPhrase::fromStatusCode(410));
        $this->assertSame('Length Required', HttpReasonPhrase::fromStatusCode(411));
        $this->assertSame('Precondition Failed', HttpReasonPhrase::fromStatusCode(412));
        $this->assertSame('Payload Too Large', HttpReasonPhrase::fromStatusCode(413));
        $this->assertSame('URI Too Long', HttpReasonPhrase::fromStatusCode(414));
        $this->assertSame('Unsupported Media Type', HttpReasonPhrase::fromStatusCode(415));
        $this->assertSame('Range Not Satisfiable', HttpReasonPhrase::fromStatusCode(416));
        $this->assertSame('Expectation Failed', HttpReasonPhrase::fromStatusCode(417));
        $this->assertSame("I'm a teapot", HttpReasonPhrase::fromStatusCode(418));
        $this->assertSame('Misdirected Request', HttpReasonPhrase::fromStatusCode(421));
        $this->assertSame('Locked', HttpReasonPhrase::fromStatusCode(423));
        $this->assertSame('Failed Dependency', HttpReasonPhrase::fromStatusCode(424));
        $this->assertSame('Too Early', HttpReasonPhrase::fromStatusCode(425));
        $this->assertSame('Upgrade Required', HttpReasonPhrase::fromStatusCode(426));
        $this->assertSame('Precondition Required', HttpReasonPhrase::fromStatusCode(428));
        $this->assertSame('Too Many Requests', HttpReasonPhrase::fromStatusCode(429));
        $this->assertSame('Request Header Fields Too Large', HttpReasonPhrase::fromStatusCode(431));
        $this->assertSame('Unavailable For Legal Reasons', HttpReasonPhrase::fromStatusCode(451));
    }

    public function test_http_reason_phrase_from_status_code_5xx_extended() : void
    {
        $this->assertSame('Not Implemented', HttpReasonPhrase::fromStatusCode(501));
        $this->assertSame('HTTP Version Not Supported', HttpReasonPhrase::fromStatusCode(505));
        $this->assertSame('Variant Also Negotiates', HttpReasonPhrase::fromStatusCode(506));
        $this->assertSame('Insufficient Storage', HttpReasonPhrase::fromStatusCode(507));
        $this->assertSame('Loop Detected', HttpReasonPhrase::fromStatusCode(508));
        $this->assertSame('Not Extended', HttpReasonPhrase::fromStatusCode(510));
        $this->assertSame('Network Authentication Required', HttpReasonPhrase::fromStatusCode(511));
    }

    public function test_http_reason_phrase_from_status_code_or_default_known() : void
    {
        $this->assertSame('OK', HttpReasonPhrase::fromStatusCodeOrDefault(200));
        $this->assertSame('Not Found', HttpReasonPhrase::fromStatusCodeOrDefault(404));
    }

    public function test_http_reason_phrase_from_status_code_or_default_unknown() : void
    {
        $this->assertSame('Unknown Status', HttpReasonPhrase::fromStatusCodeOrDefault(999));
        $this->assertSame('Unknown Status', HttpReasonPhrase::fromStatusCodeOrDefault(0));
    }

    public function test_http_reason_phrase_from_status_code_or_default_custom_default() : void
    {
        $this->assertSame('Custom', HttpReasonPhrase::fromStatusCodeOrDefault(999, 'Custom'));
        $this->assertSame('Error', HttpReasonPhrase::fromStatusCodeOrDefault(-1, 'Error'));
    }

    public function test_http_reason_phrase_is_known_valid_codes() : void
    {
        $this->assertTrue(HttpReasonPhrase::isKnown(200));
        $this->assertTrue(HttpReasonPhrase::isKnown(201));
        $this->assertTrue(HttpReasonPhrase::isKnown(301));
        $this->assertTrue(HttpReasonPhrase::isKnown(404));
        $this->assertTrue(HttpReasonPhrase::isKnown(500));
        $this->assertTrue(HttpReasonPhrase::isKnown(100));
        $this->assertTrue(HttpReasonPhrase::isKnown(418));
    }

    public function test_http_reason_phrase_is_known_invalid_codes() : void
    {
        $this->assertFalse(HttpReasonPhrase::isKnown(999));
        $this->assertFalse(HttpReasonPhrase::isKnown(0));
        $this->assertFalse(HttpReasonPhrase::isKnown(-1));
        // Note: 306 (Switch Proxy) IS defined in HttpReasonPhrase
    }

    public function test_http_reason_phrase_from_http_status_code() : void
    {
        $this->assertSame('OK', HttpReasonPhrase::fromHttpStatusCode(HttpStatusCode::OK));
        $this->assertSame('Created', HttpReasonPhrase::fromHttpStatusCode(HttpStatusCode::CREATED));
        $this->assertSame('Not Found', HttpReasonPhrase::fromHttpStatusCode(HttpStatusCode::NOT_FOUND));
        $this->assertSame('Internal Server Error', HttpReasonPhrase::fromHttpStatusCode(HttpStatusCode::INTERNAL_SERVER_ERROR));
    }

    // =========================================================================
    // 4. ContentType Enum Tests
    // =========================================================================

    public function test_content_type_application_json() : void
    {
        $this->assertSame('application/json', ContentType::APPLICATION_JSON->value);
    }

    public function test_content_type_application_xml() : void
    {
        $this->assertSame('application/xml', ContentType::APPLICATION_XML->value);
    }

    public function test_content_type_text_html() : void
    {
        $this->assertSame('text/html', ContentType::TEXT_HTML->value);
    }

    public function test_content_type_text_plain() : void
    {
        $this->assertSame('text/plain', ContentType::TEXT_PLAIN->value);
    }

    public function test_content_type_text_xml() : void
    {
        $this->assertSame('text/xml', ContentType::TEXT_XML->value);
    }

    public function test_content_type_text_css() : void
    {
        $this->assertSame('text/css', ContentType::TEXT_CSS->value);
    }

    public function test_content_type_text_csv() : void
    {
        $this->assertSame('text/csv', ContentType::TEXT_CSV->value);
    }

    public function test_content_type_application_javascript() : void
    {
        $this->assertSame('application/javascript', ContentType::APPLICATION_JAVASCRIPT->value);
    }

    public function test_content_type_application_form_urlencoded() : void
    {
        $this->assertSame('application/x-www-form-urlencoded', ContentType::APPLICATION_FORM_URLENCODED->value);
    }

    public function test_content_type_multipart_form_data() : void
    {
        $this->assertSame('multipart/form-data', ContentType::MULTIPART_FORM_DATA->value);
    }

    public function test_content_type_application_octet_stream() : void
    {
        $this->assertSame('application/octet-stream', ContentType::APPLICATION_OCTET_STREAM->value);
    }

    public function test_content_type_application_pdf() : void
    {
        $this->assertSame('application/pdf', ContentType::APPLICATION_PDF->value);
    }

    public function test_content_type_application_zip() : void
    {
        $this->assertSame('application/zip', ContentType::APPLICATION_ZIP->value);
    }

    public function test_content_type_application_gzip() : void
    {
        $this->assertSame('application/gzip', ContentType::APPLICATION_GZIP->value);
    }

    public function test_content_type_image_png() : void
    {
        $this->assertSame('image/png', ContentType::IMAGE_PNG->value);
    }

    public function test_content_type_image_jpeg() : void
    {
        $this->assertSame('image/jpeg', ContentType::IMAGE_JPEG->value);
    }

    public function test_content_type_image_gif() : void
    {
        $this->assertSame('image/gif', ContentType::IMAGE_GIF->value);
    }

    public function test_content_type_image_svg_xml() : void
    {
        $this->assertSame('image/svg+xml', ContentType::IMAGE_SVG_XML->value);
    }

    public function test_content_type_image_webp() : void
    {
        $this->assertSame('image/webp', ContentType::IMAGE_WEBP->value);
    }

    public function test_content_type_image_icon() : void
    {
        $this->assertSame('image/x-icon', ContentType::IMAGE_ICON->value);
    }

    public function test_content_type_application_atom_xml() : void
    {
        $this->assertSame('application/atom+xml', ContentType::APPLICATION_ATOM_XML->value);
    }

    public function test_content_type_application_rss_xml() : void
    {
        $this->assertSame('application/rss+xml', ContentType::APPLICATION_RSS_XML->value);
    }

    public function test_content_type_application_graphql() : void
    {
        $this->assertSame('application/graphql+json', ContentType::APPLICATION_GRAPHQL->value);
    }

    public function test_content_type_application_problem_json() : void
    {
        $this->assertSame('application/problem+json', ContentType::APPLICATION_PROBLEM_JSON->value);
    }

    public function test_content_type_application_problem_xml() : void
    {
        $this->assertSame('application/problem+xml', ContentType::APPLICATION_PROBLEM_XML->value);
    }

    public function test_content_type_application_yaml() : void
    {
        $this->assertSame('application/yaml', ContentType::APPLICATION_YAML->value);
    }

    public function test_content_type_application_toml() : void
    {
        $this->assertSame('application/toml', ContentType::APPLICATION_TOML->value);
    }

    public function test_content_type_text_markdown() : void
    {
        $this->assertSame('text/markdown', ContentType::TEXT_MARKDOWN->value);
    }

    public function test_content_type_text_calendar() : void
    {
        $this->assertSame('text/calendar', ContentType::TEXT_CALENDAR->value);
    }

    public function test_content_type_event_stream() : void
    {
        $this->assertSame('text/event-stream', ContentType::EVENT_STREAM->value);
    }

    public function test_content_type_mime_type_returns_value() : void
    {
        $this->assertSame('application/json', ContentType::APPLICATION_JSON->mimeType());
        $this->assertSame('text/html', ContentType::TEXT_HTML->mimeType());
        $this->assertSame('image/png', ContentType::IMAGE_PNG->mimeType());
    }

    public function test_content_type_mime_type_with_charset_default() : void
    {
        $this->assertSame('application/json; charset=utf-8', ContentType::APPLICATION_JSON->mimeTypeWithCharset());
        $this->assertSame('text/html; charset=utf-8', ContentType::TEXT_HTML->mimeTypeWithCharset());
        $this->assertSame('text/plain; charset=utf-8', ContentType::TEXT_PLAIN->mimeTypeWithCharset());
    }

    public function test_content_type_mime_type_with_charset_custom() : void
    {
        $this->assertSame('application/json; charset=iso-8859-1', ContentType::APPLICATION_JSON->mimeTypeWithCharset('iso-8859-1'));
        $this->assertSame('text/html; charset=ascii', ContentType::TEXT_HTML->mimeTypeWithCharset('ascii'));
    }

    public function test_content_type_mime_type_with_charset_not_applicable() : void
    {
        $this->assertSame('image/png', ContentType::IMAGE_PNG->mimeTypeWithCharset());
        $this->assertSame('application/pdf', ContentType::APPLICATION_PDF->mimeTypeWithCharset());
        $this->assertSame('application/octet-stream', ContentType::APPLICATION_OCTET_STREAM->mimeTypeWithCharset());
    }

    public function test_content_type_charset_text_types() : void
    {
        $this->assertSame('utf-8', ContentType::APPLICATION_JSON->charset());
        $this->assertSame('utf-8', ContentType::TEXT_HTML->charset());
        $this->assertSame('utf-8', ContentType::TEXT_PLAIN->charset());
        $this->assertSame('utf-8', ContentType::APPLICATION_XML->charset());
    }

    public function test_content_type_charset_binary_types() : void
    {
        $this->assertNull(ContentType::IMAGE_PNG->charset());
        $this->assertNull(ContentType::APPLICATION_PDF->charset());
        $this->assertNull(ContentType::APPLICATION_ZIP->charset());
        $this->assertNull(ContentType::APPLICATION_GZIP->charset());
    }

    public function test_content_type_is_text_based() : void
    {
        $this->assertTrue(ContentType::APPLICATION_JSON->isTextBased());
        $this->assertTrue(ContentType::TEXT_HTML->isTextBased());
        $this->assertTrue(ContentType::TEXT_PLAIN->isTextBased());
        $this->assertTrue(ContentType::APPLICATION_XML->isTextBased());
        $this->assertTrue(ContentType::TEXT_CSS->isTextBased());
        $this->assertTrue(ContentType::TEXT_CSV->isTextBased());
        $this->assertTrue(ContentType::EVENT_STREAM->isTextBased());
    }

    public function test_content_type_is_not_text_based() : void
    {
        $this->assertFalse(ContentType::IMAGE_PNG->isTextBased());
        $this->assertFalse(ContentType::APPLICATION_PDF->isTextBased());
        $this->assertFalse(ContentType::APPLICATION_ZIP->isTextBased());
        $this->assertFalse(ContentType::MULTIPART_FORM_DATA->isTextBased());
    }

    public function test_content_type_is_json() : void
    {
        $this->assertTrue(ContentType::APPLICATION_JSON->isJson());
        $this->assertTrue(ContentType::APPLICATION_GRAPHQL->isJson());
        $this->assertTrue(ContentType::APPLICATION_PROBLEM_JSON->isJson());
    }

    public function test_content_type_is_not_json() : void
    {
        $this->assertFalse(ContentType::TEXT_HTML->isJson());
        $this->assertFalse(ContentType::APPLICATION_XML->isJson());
        $this->assertFalse(ContentType::IMAGE_PNG->isJson());
    }

    public function test_content_type_is_xml() : void
    {
        $this->assertTrue(ContentType::APPLICATION_XML->isXml());
        $this->assertTrue(ContentType::TEXT_XML->isXml());
        $this->assertTrue(ContentType::APPLICATION_ATOM_XML->isXml());
        $this->assertTrue(ContentType::APPLICATION_RSS_XML->isXml());
        $this->assertTrue(ContentType::APPLICATION_PROBLEM_XML->isXml());
    }

    public function test_content_type_is_not_xml() : void
    {
        $this->assertFalse(ContentType::APPLICATION_JSON->isXml());
        $this->assertFalse(ContentType::TEXT_HTML->isXml());
        $this->assertFalse(ContentType::IMAGE_PNG->isXml());
    }

    public function test_content_type_is_image() : void
    {
        $this->assertTrue(ContentType::IMAGE_PNG->isImage());
        $this->assertTrue(ContentType::IMAGE_JPEG->isImage());
        $this->assertTrue(ContentType::IMAGE_GIF->isImage());
        $this->assertTrue(ContentType::IMAGE_SVG_XML->isImage());
        $this->assertTrue(ContentType::IMAGE_WEBP->isImage());
        $this->assertTrue(ContentType::IMAGE_ICON->isImage());
    }

    public function test_content_type_is_not_image() : void
    {
        $this->assertFalse(ContentType::APPLICATION_JSON->isImage());
        $this->assertFalse(ContentType::TEXT_HTML->isImage());
        $this->assertFalse(ContentType::APPLICATION_PDF->isImage());
    }

    public function test_content_type_is_form() : void
    {
        $this->assertTrue(ContentType::APPLICATION_FORM_URLENCODED->isForm());
        $this->assertTrue(ContentType::MULTIPART_FORM_DATA->isForm());
    }

    public function test_content_type_is_not_form() : void
    {
        $this->assertFalse(ContentType::APPLICATION_JSON->isForm());
        $this->assertFalse(ContentType::TEXT_HTML->isForm());
        $this->assertFalse(ContentType::IMAGE_PNG->isForm());
    }

    public function test_content_type_is_problem() : void
    {
        $this->assertTrue(ContentType::APPLICATION_PROBLEM_JSON->isProblem());
        $this->assertTrue(ContentType::APPLICATION_PROBLEM_XML->isProblem());
    }

    public function test_content_type_is_not_problem() : void
    {
        $this->assertFalse(ContentType::APPLICATION_JSON->isProblem());
        $this->assertFalse(ContentType::APPLICATION_XML->isProblem());
        $this->assertFalse(ContentType::TEXT_HTML->isProblem());
    }

    public function test_content_type_from_mime_type_valid() : void
    {
        $this->assertSame(ContentType::APPLICATION_JSON, ContentType::fromMimeType('application/json'));
        $this->assertSame(ContentType::TEXT_HTML, ContentType::fromMimeType('text/html'));
    }

    public function test_content_type_from_mime_type_case_insensitive() : void
    {
        $this->assertSame(ContentType::APPLICATION_JSON, ContentType::fromMimeType('APPLICATION/JSON'));
        $this->assertSame(ContentType::TEXT_HTML, ContentType::fromMimeType('Text/Html'));
    }

    public function test_content_type_from_mime_type_with_whitespace() : void
    {
        $this->assertSame(ContentType::APPLICATION_JSON, ContentType::fromMimeType('  application/json  '));
    }

    public function test_content_type_from_mime_type_invalid_throws_value_error() : void
    {
        $this->expectException(ValueError::class);
        ContentType::fromMimeType('invalid/type');
    }

    public function test_content_type_try_from_mime_type_valid() : void
    {
        $this->assertSame(ContentType::APPLICATION_JSON, ContentType::tryFromMimeType('application/json'));
        $this->assertSame(ContentType::TEXT_HTML, ContentType::tryFromMimeType('text/html'));
    }

    public function test_content_type_try_from_mime_type_invalid_returns_null() : void
    {
        $this->assertNull(ContentType::tryFromMimeType('invalid/type'));
        $this->assertNull(ContentType::tryFromMimeType(''));
    }

    public function test_content_type_is_valid() : void
    {
        $this->assertTrue(ContentType::isValid('application/json'));
        $this->assertTrue(ContentType::isValid('text/html'));
        $this->assertTrue(ContentType::isValid('IMAGE/PNG'));
    }

    public function test_content_type_is_valid_false() : void
    {
        $this->assertFalse(ContentType::isValid('invalid/type'));
        $this->assertFalse(ContentType::isValid(''));
    }

    // =========================================================================
    // 5. HeaderName Enum Tests
    // =========================================================================

    public function test_header_name_accept() : void
    {
        $this->assertSame('Accept', HeaderName::ACCEPT->value);
    }

    public function test_header_name_accept_charset() : void
    {
        $this->assertSame('Accept-Charset', HeaderName::ACCEPT_CHARSET->value);
    }

    public function test_header_name_accept_encoding() : void
    {
        $this->assertSame('Accept-Encoding', HeaderName::ACCEPT_ENCODING->value);
    }

    public function test_header_name_accept_language() : void
    {
        $this->assertSame('Accept-Language', HeaderName::ACCEPT_LANGUAGE->value);
    }

    public function test_header_name_authorization() : void
    {
        $this->assertSame('Authorization', HeaderName::AUTHORIZATION->value);
    }

    public function test_header_name_cache_control() : void
    {
        $this->assertSame('Cache-Control', HeaderName::CACHE_CONTROL->value);
    }

    public function test_header_name_connection() : void
    {
        $this->assertSame('Connection', HeaderName::CONNECTION->value);
    }

    public function test_header_name_content_length() : void
    {
        $this->assertSame('Content-Length', HeaderName::CONTENT_LENGTH->value);
    }

    public function test_header_name_content_type() : void
    {
        $this->assertSame('Content-Type', HeaderName::CONTENT_TYPE->value);
    }

    public function test_header_name_cookie() : void
    {
        $this->assertSame('Cookie', HeaderName::COOKIE->value);
    }

    public function test_header_name_date() : void
    {
        $this->assertSame('Date', HeaderName::DATE->value);
    }

    public function test_header_name_host() : void
    {
        $this->assertSame('Host', HeaderName::HOST->value);
    }

    public function test_header_name_origin() : void
    {
        $this->assertSame('Origin', HeaderName::ORIGIN->value);
    }

    public function test_header_name_user_agent() : void
    {
        $this->assertSame('User-Agent', HeaderName::USER_AGENT->value);
    }

    public function test_header_name_www_authenticate() : void
    {
        $this->assertSame('WWW-Authenticate', HeaderName::WWW_AUTHENTICATE->value);
    }

    public function test_header_name_set_cookie() : void
    {
        $this->assertSame('Set-Cookie', HeaderName::SET_COOKIE->value);
    }

    public function test_header_name_strict_transport_security() : void
    {
        $this->assertSame('Strict-Transport-Security', HeaderName::STRICT_TRANSPORT_SECURITY->value);
    }

    public function test_header_name_content_security_policy() : void
    {
        $this->assertSame('Content-Security-Policy', HeaderName::CONTENT_SECURITY_POLICY->value);
    }

    public function test_header_name_access_control_allow_origin() : void
    {
        $this->assertSame('Access-Control-Allow-Origin', HeaderName::ACCESS_CONTROL_ALLOW_ORIGIN->value);
    }

    public function test_header_name_access_control_allow_methods() : void
    {
        $this->assertSame('Access-Control-Allow-Methods', HeaderName::ACCESS_CONTROL_ALLOW_METHODS->value);
    }

    public function test_header_name_access_control_allow_headers() : void
    {
        $this->assertSame('Access-Control-Allow-Headers', HeaderName::ACCESS_CONTROL_ALLOW_HEADERS->value);
    }

    public function test_header_name_x_requested_with() : void
    {
        $this->assertSame('X-Requested-With', HeaderName::X_REQUESTED_WITH->value);
    }

    public function test_header_name_x_csrf_token() : void
    {
        $this->assertSame('X-CSRF-Token', HeaderName::X_CSRF_TOKEN->value);
    }

    public function test_header_name_x_content_type_options() : void
    {
        $this->assertSame('X-Content-Type-Options', HeaderName::X_CONTENT_TYPE_OPTIONS->value);
    }

    public function test_header_name_to_string() : void
    {
        $this->assertSame('Accept', HeaderName::ACCEPT->toString());
        $this->assertSame('Content-Type', HeaderName::CONTENT_TYPE->toString());
        $this->assertSame('Authorization', HeaderName::AUTHORIZATION->toString());
    }

    public function test_header_name_is_request_header() : void
    {
        $this->assertTrue(HeaderName::ACCEPT->isRequestHeader());
        $this->assertTrue(HeaderName::AUTHORIZATION->isRequestHeader());
        $this->assertTrue(HeaderName::CONTENT_TYPE->isRequestHeader());
        $this->assertTrue(HeaderName::USER_AGENT->isRequestHeader());
        $this->assertTrue(HeaderName::COOKIE->isRequestHeader());
        $this->assertTrue(HeaderName::HOST->isRequestHeader());
        $this->assertTrue(HeaderName::ORIGIN->isRequestHeader());
    }

    public function test_header_name_is_not_request_header() : void
    {
        $this->assertFalse(HeaderName::SET_COOKIE->isRequestHeader());
        $this->assertFalse(HeaderName::WWW_AUTHENTICATE->isRequestHeader());
        $this->assertFalse(HeaderName::SERVER->isRequestHeader());
        $this->assertFalse(HeaderName::ACCESS_CONTROL_ALLOW_ORIGIN->isRequestHeader());
    }

    public function test_header_name_is_response_header() : void
    {
        $this->assertTrue(HeaderName::SET_COOKIE->isResponseHeader());
        $this->assertTrue(HeaderName::WWW_AUTHENTICATE->isResponseHeader());
        $this->assertTrue(HeaderName::SERVER->isResponseHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_ALLOW_ORIGIN->isResponseHeader());
        $this->assertTrue(HeaderName::ETAG->isResponseHeader());
        $this->assertTrue(HeaderName::LOCATION->isResponseHeader());
    }

    public function test_header_name_is_not_response_header() : void
    {
        $this->assertFalse(HeaderName::ACCEPT->isResponseHeader());
        $this->assertFalse(HeaderName::AUTHORIZATION->isResponseHeader());
        $this->assertFalse(HeaderName::USER_AGENT->isResponseHeader());
        $this->assertFalse(HeaderName::COOKIE->isResponseHeader());
    }

    public function test_header_name_is_bidirectional() : void
    {
        // In this enum, all headers are classified as either request-only or response-only.
        // isBidirectional() returns true only when a header is NEITHER request nor response.
        // Currently, there are no bidirectional headers in this enum.
        foreach (HeaderName::cases() as $case) {
            $this->assertFalse(
                $case->isBidirectional(),
                "Header {$case->name} should not be bidirectional",
            );
        }
    }

    public function test_header_name_is_not_bidirectional_for_request_headers() : void
    {
        $this->assertFalse(HeaderName::ACCEPT->isBidirectional());
        $this->assertFalse(HeaderName::USER_AGENT->isBidirectional());
    }

    public function test_header_name_is_not_bidirectional_for_response_headers() : void
    {
        $this->assertFalse(HeaderName::SET_COOKIE->isBidirectional());
        $this->assertFalse(HeaderName::SERVER->isBidirectional());
    }

    public function test_header_name_is_security_header() : void
    {
        $this->assertTrue(HeaderName::AUTHORIZATION->isSecurityHeader());
        $this->assertTrue(HeaderName::PROXY_AUTHORIZATION->isSecurityHeader());
        $this->assertTrue(HeaderName::WWW_AUTHENTICATE->isSecurityHeader());
        $this->assertTrue(HeaderName::STRICT_TRANSPORT_SECURITY->isSecurityHeader());
        $this->assertTrue(HeaderName::CONTENT_SECURITY_POLICY->isSecurityHeader());
        $this->assertTrue(HeaderName::X_CSRF_TOKEN->isSecurityHeader());
        $this->assertTrue(HeaderName::X_XSS_PROTECTION->isSecurityHeader());
        $this->assertTrue(HeaderName::X_FRAME_OPTIONS->isSecurityHeader());
        $this->assertTrue(HeaderName::X_CONTENT_TYPE_OPTIONS->isSecurityHeader());
        $this->assertTrue(HeaderName::REFERRER_POLICY->isSecurityHeader());
        $this->assertTrue(HeaderName::PERMISSIONS_POLICY->isSecurityHeader());
        $this->assertTrue(HeaderName::CROSS_ORIGIN_OPENER_POLICY->isSecurityHeader());
        $this->assertTrue(HeaderName::CROSS_ORIGIN_RESOURCE_POLICY->isSecurityHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_ALLOW_ORIGIN->isSecurityHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_ALLOW_METHODS->isSecurityHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_ALLOW_HEADERS->isSecurityHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_ALLOW_CREDENTIALS->isSecurityHeader());
    }

    public function test_header_name_is_not_security_header() : void
    {
        $this->assertFalse(HeaderName::ACCEPT->isSecurityHeader());
        $this->assertFalse(HeaderName::USER_AGENT->isSecurityHeader());
        $this->assertFalse(HeaderName::SERVER->isSecurityHeader());
    }

    public function test_header_name_is_cors_header() : void
    {
        $this->assertTrue(HeaderName::ORIGIN->isCorsHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_ALLOW_ORIGIN->isCorsHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_ALLOW_METHODS->isCorsHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_ALLOW_HEADERS->isCorsHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_ALLOW_CREDENTIALS->isCorsHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_EXPOSE_HEADERS->isCorsHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_MAX_AGE->isCorsHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_REQUEST_METHOD->isCorsHeader());
        $this->assertTrue(HeaderName::ACCESS_CONTROL_REQUEST_HEADERS->isCorsHeader());
    }

    public function test_header_name_is_not_cors_header() : void
    {
        $this->assertFalse(HeaderName::ACCEPT->isCorsHeader());
        $this->assertFalse(HeaderName::USER_AGENT->isCorsHeader());
        $this->assertFalse(HeaderName::SET_COOKIE->isCorsHeader());
    }

    public function test_header_name_is_caching_header() : void
    {
        $this->assertTrue(HeaderName::CACHE_CONTROL->isCachingHeader());
        $this->assertTrue(HeaderName::ETAG->isCachingHeader());
        $this->assertTrue(HeaderName::EXPIRES->isCachingHeader());
        $this->assertTrue(HeaderName::LAST_MODIFIED->isCachingHeader());
        $this->assertTrue(HeaderName::IF_MATCH->isCachingHeader());
        $this->assertTrue(HeaderName::IF_MODIFIED_SINCE->isCachingHeader());
        $this->assertTrue(HeaderName::IF_NONE_MATCH->isCachingHeader());
        $this->assertTrue(HeaderName::IF_RANGE->isCachingHeader());
        $this->assertTrue(HeaderName::IF_UNMODIFIED_SINCE->isCachingHeader());
        $this->assertTrue(HeaderName::PRAGMA->isCachingHeader());
        $this->assertTrue(HeaderName::VARY->isCachingHeader());
        $this->assertTrue(HeaderName::CDN_CACHE_CONTROL->isCachingHeader());
    }

    public function test_header_name_is_not_caching_header() : void
    {
        $this->assertFalse(HeaderName::ACCEPT->isCachingHeader());
        $this->assertFalse(HeaderName::AUTHORIZATION->isCachingHeader());
        $this->assertFalse(HeaderName::CONTENT_TYPE->isCachingHeader());
    }

    public function test_header_name_from_name_valid_exact_case() : void
    {
        $this->assertSame(HeaderName::ACCEPT, HeaderName::fromName('Accept'));
        $this->assertSame(HeaderName::CONTENT_TYPE, HeaderName::fromName('Content-Type'));
    }

    public function test_header_name_from_name_valid_lower_case() : void
    {
        $this->assertSame(HeaderName::ACCEPT, HeaderName::fromName('accept'));
        $this->assertSame(HeaderName::CONTENT_TYPE, HeaderName::fromName('content-type'));
    }

    public function test_header_name_from_name_valid_mixed_case() : void
    {
        $this->assertSame(HeaderName::ACCEPT, HeaderName::fromName('ACCEPT'));
        $this->assertSame(HeaderName::USER_AGENT, HeaderName::fromName('user-agent'));
    }

    public function test_header_name_from_name_with_whitespace() : void
    {
        $this->assertSame(HeaderName::ACCEPT, HeaderName::fromName(' Accept '));
        $this->assertSame(HeaderName::CONTENT_TYPE, HeaderName::fromName('  Content-Type  '));
    }

    public function test_header_name_from_name_invalid_throws_value_error() : void
    {
        $this->expectException(ValueError::class);
        HeaderName::fromName('X-Invalid-Header');
    }

    public function test_header_name_try_from_name_valid() : void
    {
        $this->assertSame(HeaderName::ACCEPT, HeaderName::tryFromName('Accept'));
        $this->assertSame(HeaderName::CONTENT_TYPE, HeaderName::tryFromName('content-type'));
    }

    public function test_header_name_try_from_name_invalid_returns_null() : void
    {
        $this->assertNull(HeaderName::tryFromName('X-Invalid-Header'));
        $this->assertNull(HeaderName::tryFromName(''));
    }

    public function test_header_name_is_valid() : void
    {
        $this->assertTrue(HeaderName::isValid('Accept'));
        $this->assertTrue(HeaderName::isValid('content-type'));
        $this->assertTrue(HeaderName::isValid('USER-AGENT'));
    }

    public function test_header_name_is_valid_false() : void
    {
        $this->assertFalse(HeaderName::isValid('X-Invalid-Header'));
        $this->assertFalse(HeaderName::isValid(''));
    }

    // =========================================================================
    // 6. RequestOption Enum Tests
    // =========================================================================

    public function test_request_option_all_20_cases_exist() : void
    {
        $cases = RequestOption::cases();
        $this->assertCount(20, $cases);
    }

    public function test_request_option_timeout() : void
    {
        $this->assertSame('timeout', RequestOption::TIMEOUT->value);
    }

    public function test_request_option_connect_timeout() : void
    {
        $this->assertSame('connect_timeout', RequestOption::CONNECT_TIMEOUT->value);
    }

    public function test_request_option_verify_ssl() : void
    {
        $this->assertSame('verify_ssl', RequestOption::VERIFY_SSL->value);
    }

    public function test_request_option_proxy() : void
    {
        $this->assertSame('proxy', RequestOption::PROXY->value);
    }

    public function test_request_option_headers_only() : void
    {
        $this->assertSame('headers_only', RequestOption::HEADERS_ONLY->value);
    }

    public function test_request_option_follow_redirects() : void
    {
        $this->assertSame('follow_redirects', RequestOption::FOLLOW_REDIRECTS->value);
    }

    public function test_request_option_max_redirects() : void
    {
        $this->assertSame('max_redirects', RequestOption::MAX_REDIRECTS->value);
    }

    public function test_request_option_retry_count() : void
    {
        $this->assertSame('retry_count', RequestOption::RETRY_COUNT->value);
    }

    public function test_request_option_retry_delay() : void
    {
        $this->assertSame('retry_delay', RequestOption::RETRY_DELAY->value);
    }

    public function test_request_option_ssl_cert() : void
    {
        $this->assertSame('ssl_cert', RequestOption::SSL_CERT->value);
    }

    public function test_request_option_ssl_key() : void
    {
        $this->assertSame('ssl_key', RequestOption::SSL_KEY->value);
    }

    public function test_request_option_ssl_ca_path() : void
    {
        $this->assertSame('ssl_ca_path', RequestOption::SSL_CA_PATH->value);
    }

    public function test_request_option_ssl_ca_file() : void
    {
        $this->assertSame('ssl_ca_file', RequestOption::SSL_CA_FILE->value);
    }

    public function test_request_option_http_version() : void
    {
        $this->assertSame('http_version', RequestOption::HTTP_VERSION->value);
    }

    public function test_request_option_decode_content() : void
    {
        $this->assertSame('decode_content', RequestOption::DECODE_CONTENT->value);
    }

    public function test_request_option_stream_response() : void
    {
        $this->assertSame('stream_response', RequestOption::STREAM_RESPONSE->value);
    }

    public function test_request_option_on_headers() : void
    {
        $this->assertSame('on_headers', RequestOption::ON_HEADERS->value);
    }

    public function test_request_option_on_progress() : void
    {
        $this->assertSame('on_progress', RequestOption::ON_PROGRESS->value);
    }

    public function test_request_option_on_stats() : void
    {
        $this->assertSame('on_stats', RequestOption::ON_STATS->value);
    }

    public function test_request_option_synchronous() : void
    {
        $this->assertSame('synchronous', RequestOption::SYNCHRONOUS->value);
    }

    public function test_request_option_default_value_type_float() : void
    {
        $this->assertSame('float', RequestOption::TIMEOUT->defaultValueType());
        $this->assertSame('float', RequestOption::CONNECT_TIMEOUT->defaultValueType());
        $this->assertSame('float', RequestOption::RETRY_DELAY->defaultValueType());
    }

    public function test_request_option_default_value_type_bool() : void
    {
        $this->assertSame('bool', RequestOption::VERIFY_SSL->defaultValueType());
        $this->assertSame('bool', RequestOption::HEADERS_ONLY->defaultValueType());
        $this->assertSame('bool', RequestOption::FOLLOW_REDIRECTS->defaultValueType());
        $this->assertSame('bool', RequestOption::DECODE_CONTENT->defaultValueType());
        $this->assertSame('bool', RequestOption::STREAM_RESPONSE->defaultValueType());
        $this->assertSame('bool', RequestOption::SYNCHRONOUS->defaultValueType());
    }

    public function test_request_option_default_value_type_int() : void
    {
        $this->assertSame('int', RequestOption::MAX_REDIRECTS->defaultValueType());
        $this->assertSame('int', RequestOption::RETRY_COUNT->defaultValueType());
        $this->assertSame('int', RequestOption::HTTP_VERSION->defaultValueType());
    }

    public function test_request_option_default_value_type_string() : void
    {
        $this->assertSame('string', RequestOption::PROXY->defaultValueType());
        $this->assertSame('string', RequestOption::SSL_CERT->defaultValueType());
        $this->assertSame('string', RequestOption::SSL_KEY->defaultValueType());
        $this->assertSame('string', RequestOption::SSL_CA_PATH->defaultValueType());
        $this->assertSame('string', RequestOption::SSL_CA_FILE->defaultValueType());
    }

    public function test_request_option_default_value_type_callable() : void
    {
        $this->assertSame('callable', RequestOption::ON_HEADERS->defaultValueType());
        $this->assertSame('callable', RequestOption::ON_PROGRESS->defaultValueType());
        $this->assertSame('callable', RequestOption::ON_STATS->defaultValueType());
    }

    public function test_request_option_default_value_timeout() : void
    {
        $this->assertSame(30.0, RequestOption::TIMEOUT->defaultValue());
        $this->assertSame(10.0, RequestOption::CONNECT_TIMEOUT->defaultValue());
    }

    public function test_request_option_default_value_boolean() : void
    {
        $this->assertTrue(RequestOption::VERIFY_SSL->defaultValue());
        $this->assertFalse(RequestOption::HEADERS_ONLY->defaultValue());
        $this->assertTrue(RequestOption::FOLLOW_REDIRECTS->defaultValue());
        $this->assertTrue(RequestOption::DECODE_CONTENT->defaultValue());
        $this->assertFalse(RequestOption::STREAM_RESPONSE->defaultValue());
        $this->assertTrue(RequestOption::SYNCHRONOUS->defaultValue());
    }

    public function test_request_option_default_value_int() : void
    {
        $this->assertSame(5, RequestOption::MAX_REDIRECTS->defaultValue());
        $this->assertSame(0, RequestOption::RETRY_COUNT->defaultValue());
        $this->assertSame(0, RequestOption::HTTP_VERSION->defaultValue());
    }

    public function test_request_option_default_value_null() : void
    {
        $this->assertNull(RequestOption::PROXY->defaultValue());
        $this->assertNull(RequestOption::SSL_CERT->defaultValue());
        $this->assertNull(RequestOption::SSL_KEY->defaultValue());
        $this->assertNull(RequestOption::SSL_CA_PATH->defaultValue());
        $this->assertNull(RequestOption::SSL_CA_FILE->defaultValue());
        $this->assertNull(RequestOption::ON_HEADERS->defaultValue());
        $this->assertNull(RequestOption::ON_PROGRESS->defaultValue());
        $this->assertNull(RequestOption::ON_STATS->defaultValue());
    }

    public function test_request_option_default_value_float_delay() : void
    {
        $this->assertSame(1.0, RequestOption::RETRY_DELAY->defaultValue());
    }

    public function test_request_option_is_boolean() : void
    {
        $this->assertTrue(RequestOption::VERIFY_SSL->isBoolean());
        $this->assertTrue(RequestOption::HEADERS_ONLY->isBoolean());
        $this->assertTrue(RequestOption::FOLLOW_REDIRECTS->isBoolean());
        $this->assertTrue(RequestOption::DECODE_CONTENT->isBoolean());
        $this->assertTrue(RequestOption::STREAM_RESPONSE->isBoolean());
        $this->assertTrue(RequestOption::SYNCHRONOUS->isBoolean());
    }

    public function test_request_option_is_not_boolean() : void
    {
        $this->assertFalse(RequestOption::TIMEOUT->isBoolean());
        $this->assertFalse(RequestOption::PROXY->isBoolean());
        $this->assertFalse(RequestOption::ON_HEADERS->isBoolean());
    }

    public function test_request_option_is_numeric() : void
    {
        $this->assertTrue(RequestOption::TIMEOUT->isNumeric());
        $this->assertTrue(RequestOption::CONNECT_TIMEOUT->isNumeric());
        $this->assertTrue(RequestOption::MAX_REDIRECTS->isNumeric());
        $this->assertTrue(RequestOption::RETRY_COUNT->isNumeric());
        $this->assertTrue(RequestOption::RETRY_DELAY->isNumeric());
        $this->assertTrue(RequestOption::HTTP_VERSION->isNumeric());
    }

    public function test_request_option_is_not_numeric() : void
    {
        $this->assertFalse(RequestOption::VERIFY_SSL->isNumeric());
        $this->assertFalse(RequestOption::PROXY->isNumeric());
        $this->assertFalse(RequestOption::ON_HEADERS->isNumeric());
    }

    public function test_request_option_is_callable() : void
    {
        $this->assertTrue(RequestOption::ON_HEADERS->isCallable());
        $this->assertTrue(RequestOption::ON_PROGRESS->isCallable());
        $this->assertTrue(RequestOption::ON_STATS->isCallable());
    }

    public function test_request_option_is_not_callable() : void
    {
        $this->assertFalse(RequestOption::TIMEOUT->isCallable());
        $this->assertFalse(RequestOption::VERIFY_SSL->isCallable());
        $this->assertFalse(RequestOption::PROXY->isCallable());
    }

    public function test_request_option_is_ssl_related() : void
    {
        $this->assertTrue(RequestOption::VERIFY_SSL->isSslRelated());
        $this->assertTrue(RequestOption::SSL_CERT->isSslRelated());
        $this->assertTrue(RequestOption::SSL_KEY->isSslRelated());
        $this->assertTrue(RequestOption::SSL_CA_PATH->isSslRelated());
        $this->assertTrue(RequestOption::SSL_CA_FILE->isSslRelated());
    }

    public function test_request_option_is_not_ssl_related() : void
    {
        $this->assertFalse(RequestOption::TIMEOUT->isSslRelated());
        $this->assertFalse(RequestOption::PROXY->isSslRelated());
        $this->assertFalse(RequestOption::FOLLOW_REDIRECTS->isSslRelated());
    }

    public function test_request_option_is_timeout() : void
    {
        $this->assertTrue(RequestOption::TIMEOUT->isTimeout());
        $this->assertTrue(RequestOption::CONNECT_TIMEOUT->isTimeout());
    }

    public function test_request_option_is_not_timeout() : void
    {
        $this->assertFalse(RequestOption::RETRY_DELAY->isTimeout());
        $this->assertFalse(RequestOption::VERIFY_SSL->isTimeout());
        $this->assertFalse(RequestOption::PROXY->isTimeout());
    }

    public function test_request_option_is_retry_related() : void
    {
        $this->assertTrue(RequestOption::RETRY_COUNT->isRetryRelated());
        $this->assertTrue(RequestOption::RETRY_DELAY->isRetryRelated());
    }

    public function test_request_option_is_not_retry_related() : void
    {
        $this->assertFalse(RequestOption::TIMEOUT->isRetryRelated());
        $this->assertFalse(RequestOption::MAX_REDIRECTS->isRetryRelated());
        $this->assertFalse(RequestOption::VERIFY_SSL->isRetryRelated());
    }

    public function test_request_option_is_event_callback() : void
    {
        $this->assertTrue(RequestOption::ON_HEADERS->isEventCallback());
        $this->assertTrue(RequestOption::ON_PROGRESS->isEventCallback());
        $this->assertTrue(RequestOption::ON_STATS->isEventCallback());
    }

    public function test_request_option_is_not_event_callback() : void
    {
        $this->assertFalse(RequestOption::TIMEOUT->isEventCallback());
        $this->assertFalse(RequestOption::VERIFY_SSL->isEventCallback());
        $this->assertFalse(RequestOption::PROXY->isEventCallback());
    }

    public function test_request_option_from_name_valid() : void
    {
        $this->assertSame(RequestOption::TIMEOUT, RequestOption::fromName('timeout'));
        $this->assertSame(RequestOption::VERIFY_SSL, RequestOption::fromName('verify_ssl'));
        $this->assertSame(RequestOption::ON_HEADERS, RequestOption::fromName('on_headers'));
    }

    public function test_request_option_from_name_with_whitespace() : void
    {
        $this->assertSame(RequestOption::TIMEOUT, RequestOption::fromName(' timeout '));
        $this->assertSame(RequestOption::PROXY, RequestOption::fromName('  proxy  '));
    }

    public function test_request_option_from_name_invalid_throws_value_error() : void
    {
        $this->expectException(ValueError::class);
        RequestOption::fromName('invalid_option');
    }

    public function test_request_option_try_from_name_valid() : void
    {
        $this->assertSame(RequestOption::TIMEOUT, RequestOption::tryFromName('timeout'));
        $this->assertSame(RequestOption::VERIFY_SSL, RequestOption::tryFromName('verify_ssl'));
    }

    public function test_request_option_try_from_name_invalid_returns_null() : void
    {
        $this->assertNull(RequestOption::tryFromName('invalid_option'));
        $this->assertNull(RequestOption::tryFromName(''));
    }

    public function test_request_option_is_valid() : void
    {
        $this->assertTrue(RequestOption::isValid('timeout'));
        $this->assertTrue(RequestOption::isValid('verify_ssl'));
        $this->assertTrue(RequestOption::isValid('on_headers'));
    }

    public function test_request_option_is_valid_false() : void
    {
        $this->assertFalse(RequestOption::isValid('invalid_option'));
        $this->assertFalse(RequestOption::isValid(''));
    }
}
