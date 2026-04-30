<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI;

use Avax\Components\HTTP\URI\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

/**
 * Unit tests for Uri component.
 *
 * Verifies immutable URI parsing, manipulation, and rendering.
 */
final class UriTest extends TestCase
{
    // ========== HAPPY PATH: Valid URIs with all components ==========

    public function test_it_parses_full_uri_when_all_components_present() : void
    {
        // Arrange
        $uriString = 'https://user:pass@example.com:8080/path?query=value#fragment';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: 'https', actual: $uri->getScheme());
        self::assertSame(expected: 'user:pass', actual: $uri->getUserInfo());
        self::assertSame(expected: 'example.com', actual: $uri->getHost());
        self::assertSame(expected: 8080, actual: $uri->getPort());
        self::assertSame(expected: '/path', actual: $uri->getPath());
        self::assertSame(expected: 'query=value', actual: $uri->getQuery());
        self::assertSame(expected: 'fragment', actual: $uri->getFragment());
        self::assertSame(expected: 'https://user:pass@example.com:8080/path?query=value#fragment', actual: (string) $uri);
    }

    public function test_it_parses_uri_without_port_when_using_default_port() : void
    {
        // Arrange
        $httpsUri = 'https://example.com:443/api';
        $httpUri  = 'http://example.com:80/api';

        // Act & Assert
        $https = Uri::fromString(uri: $httpsUri);
        self::assertNull(actual: $https->getPort());
        self::assertSame(expected: 'https://example.com/api', actual: (string) $https);

        $http = Uri::fromString(uri: $httpUri);
        self::assertNull(actual: $http->getPort());
        self::assertSame(expected: 'http://example.com/api', actual: (string) $http);
    }

    public function test_it_parses_uri_with_non_default_port_when_specified() : void
    {
        // Arrange
        $uriString = 'https://example.com:8443/secure';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: 8443, actual: $uri->getPort());
        self::assertSame(expected: 'https://example.com:8443/secure', actual: (string) $uri);
    }

    public function test_it_parses_uri_with_multiple_query_parameters() : void
    {
        // Arrange
        $uriString = 'https://example.com?foo=bar&baz=qux&alpha=beta';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: 'foo=bar&baz=qux&alpha=beta', actual: $uri->getQuery());
        self::assertSame(expected: 'https://example.com/?foo=bar&baz=qux&alpha=beta', actual: (string) $uri);
    }

    public function test_it_normalizes_host_to_lowercase() : void
    {
        // Arrange
        $uriString = 'https://EXAMPLE.COM';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: 'example.com', actual: $uri->getHost());
        self::assertSame(expected: 'https://example.com/', actual: (string) $uri);
    }

    public function test_it_normalizes_path_segments() : void
    {
        // Arrange: Path with . and .. should be normalized
        $uriString = 'https://example.com/./path/../other';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: '/other', actual: $uri->getPath());
        self::assertSame(expected: 'https://example.com/other', actual: (string) $uri);
    }

    public function test_it_encodes_path_segments() : void
    {
        // Arrange
        $uriString = 'https://example.com/user space/file.txt';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert - space should be encoded
        self::assertStringContainsString(needle: 'user%20space', haystack: (string) $uri);
    }

    // ========== HAPPY PATH: URIs with optional components ==========

    public function test_it_parses_uri_without_user_info() : void
    {
        // Arrange
        $uriString = 'https://example.com/api';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: '', actual: $uri->getUserInfo());
        self::assertSame(expected: 'https://example.com/api', actual: (string) $uri);
    }

    public function test_it_parses_uri_without_port() : void
    {
        // Arrange
        $uriString = 'https://example.com/api';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertNull(actual: $uri->getPort());
        self::assertSame(expected: 'https://example.com/api', actual: (string) $uri);
    }

    public function test_it_parses_uri_without_query() : void
    {
        // Arrange
        $uriString = 'https://example.com/path';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: '', actual: $uri->getQuery());
        self::assertSame(expected: 'https://example.com/path', actual: (string) $uri);
    }

    public function test_it_parses_uri_without_fragment() : void
    {
        // Arrange
        $uriString = 'https://example.com/path';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: '', actual: $uri->getFragment());
        self::assertSame(expected: 'https://example.com/path', actual: (string) $uri);
    }

    public function test_it_parses_uri_with_only_user_without_password() : void
    {
        // Arrange
        $uriString = 'https://user@example.com';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: 'user', actual: $uri->getUserInfo());
        self::assertSame(expected: 'https://user@example.com/', actual: (string) $uri);
    }

    // ========== HAPPY PATH: Relative and minimal URIs ==========

    public function test_it_parses_relative_path_only() : void
    {
        // Arrange
        $uriString = '/api/users';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: '', actual: $uri->getScheme());
        self::assertSame(expected: '', actual: $uri->getHost());
        self::assertSame(expected: '/api/users', actual: $uri->getPath());
        self::assertSame(expected: '/api/users', actual: (string) $uri);
    }

    public function test_it_parses_path_with_query_no_scheme() : void
    {
        // Arrange
        $uriString = '/search?q=test';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: '/search', actual: $uri->getPath());
        self::assertSame(expected: 'q=test', actual: $uri->getQuery());
    }

    // ========== IMMUTABILITY: with* methods return new instances ==========

    public function test_it_returns_new_instance_when_withScheme_called() : void
    {
        // Arrange
        $originalUri = Uri::fromString(uri: 'http://example.com');

        // Act
        $newUri = $originalUri->withScheme(scheme: 'https');

        // Assert
        self::assertNotSame(expected: $originalUri, actual: $newUri);
        self::assertSame(expected: 'http', actual: $originalUri->getScheme());
        self::assertSame(expected: 'https', actual: $newUri->getScheme());
    }

    public function test_it_returns_new_instance_when_withHost_called() : void
    {
        // Arrange
        $originalUri = Uri::fromString(uri: 'https://example.com');

        // Act
        $newUri = $originalUri->withHost(host: 'new.com');

        // Assert
        self::assertNotSame(expected: $originalUri, actual: $newUri);
        self::assertSame(expected: 'example.com', actual: $originalUri->getHost());
        self::assertSame(expected: 'new.com', actual: $newUri->getHost());
    }

    public function test_it_returns_new_instance_when_withPath_called() : void
    {
        // Arrange
        $originalUri = Uri::fromString(uri: 'https://example.com/old');

        // Act
        $newUri = $originalUri->withPath(path: '/new');

        // Assert
        self::assertNotSame(expected: $originalUri, actual: $newUri);
        self::assertSame(expected: '/old', actual: $originalUri->getPath());
        self::assertSame(expected: '/new', actual: $newUri->getPath());
    }

    public function test_it_returns_new_instance_when_withQuery_called() : void
    {
        // Arrange
        $originalUri = Uri::fromString(uri: 'https://example.com?old=value');

        // Act
        $newUri = $originalUri->withQuery(query: 'new=value');

        // Assert
        self::assertNotSame(expected: $originalUri, actual: $newUri);
        self::assertSame(expected: 'old=value', actual: $originalUri->getQuery());
        self::assertSame(expected: 'new=value', actual: $newUri->getQuery());
    }

    public function test_it_returns_new_instance_when_withFragment_called() : void
    {
        // Arrange
        $originalUri = Uri::fromString(uri: 'https://example.com#old');

        // Act
        $newUri = $originalUri->withFragment(fragment: 'new');

        // Assert
        self::assertNotSame(expected: $originalUri, actual: $newUri);
        self::assertSame(expected: 'old', actual: $originalUri->getFragment());
        self::assertSame(expected: 'new', actual: $newUri->getFragment());
    }

    public function test_it_chains_multiple_with_methods() : void
    {
        // Arrange
        $originalUri = Uri::fromString(uri: 'http://example.com/old?old=val#old');

        // Act
        $newUri = $originalUri
            ->withScheme(scheme: 'https')
            ->withHost(host: 'new.com')
            ->withPath(path: '/new')
            ->withQuery(query: 'new=val')
            ->withFragment(fragment: 'new');

        // Assert
        self::assertSame(expected: 'https://new.com/new?new=val#new', actual: (string) $newUri);
        // Original unchanged
        self::assertSame(expected: 'http://example.com/old?old=val#old', actual: (string) $originalUri);
    }

    // ========== FAILURE: Invalid schemes ==========

    public function test_it_throws_when_scheme_is_invalid() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        Uri::fromString(uri: 'invalid://example.com');
    }

    public function test_it_throws_when_withScheme_given_invalid_scheme() : void
    {
        // Arrange
        $uri = Uri::fromString(uri: 'https://example.com');

        // Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        $uri->withScheme(scheme: 'invalid-protocol');
    }

    // ========== FAILURE: Invalid hosts ==========

    public function test_it_throws_when_host_is_invalid() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        Uri::fromString(uri: 'https://invalid..host');
    }

    public function test_it_throws_when_withHost_given_empty_string() : void
    {
        // Arrange
        $uri = Uri::fromString(uri: 'https://example.com');

        // Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        $uri->withHost(host: '');
    }

    // ========== FAILURE: Invalid ports ==========

    public function test_it_throws_when_port_exceeds_maximum() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        Uri::fromString(uri: 'https://example.com:99999');
    }

    public function test_it_throws_when_port_is_zero() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        Uri::fromString(uri: 'https://example.com:0');
    }

    // ========== EDGE CASES: Empty and minimal values ==========

    public function test_it_handles_empty_query_string() : void
    {
        // Arrange
        $uriString = 'https://example.com?';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: '', actual: $uri->getQuery());
    }

    public function test_it_handles_path_with_trailing_slash() : void
    {
        // Arrange
        $uriString = 'https://example.com/';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: '/', actual: $uri->getPath());
        self::assertSame(expected: 'https://example.com/', actual: (string) $uri);
    }

    public function test_it_handles_root_path() : void
    {
        // Arrange
        $uriString = 'https://example.com';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: '/', actual: $uri->getPath());
    }

    public function test_it_preserves_empty_path_segment() : void
    {
        // Arrange
        $uriString = 'https://example.com/api//users';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert - leading slash preserved, empty segments removed
        self::assertSame(expected: '/api/users', actual: $uri->getPath());
    }

    public function test_it_handles_query_with_empty_value() : void
    {
        // Arrange
        $uriString = 'https://example.com?key=';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertSame(expected: 'key=', actual: $uri->getQuery());
    }

    // ========== EDGE CASES: Special characters in components ==========

    public function test_it_encodes_special_characters_in_path() : void
    {
        // Arrange
        $uriString = 'https://example.com/path with spaces';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertStringContainsString(needle: '%20', haystack: (string) $uri);
    }

    public function test_it_double_encodes_percent_sequences() : void
    {
        // Arrange
        $uriString = 'https://example.com/path%20with%20spaces';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        // Percent signs get encoded again
        $rendered = (string) $uri;
        self::assertStringContainsString(needle: '%25', haystack: $rendered);
    }

    // ========== PSR-7 INTERFACE COMPATIBILITY ==========

    public function test_it_implements_psr7_uri_interface() : void
    {
        // Arrange & Act
        $uri = Uri::fromString(uri: 'https://example.com');

        // Assert
        self::assertInstanceOf(expected: UriInterface::class, actual: $uri);
    }

    public function test_it_supports_psr7_withUserInfo_method() : void
    {
        // Arrange
        $uri = Uri::fromString(uri: 'https://example.com');

        // Act
        $newUri = $uri->withUserInfo(user: 'admin', password: 'secret');

        // Assert
        self::assertSame(expected: 'admin:secret', actual: $newUri->getUserInfo());
        self::assertSame(expected: 'https://admin:secret@example.com/', actual: (string) $newUri);
    }

    public function test_it_supports_psr7_withPort_method() : void
    {
        // Arrange
        $uri = Uri::fromString(uri: 'https://example.com');

        // Act
        $newUri = $uri->withPort(port: 8443);

        // Assert
        self::assertSame(expected: 8443, actual: $newUri->getPort());
        self::assertSame(expected: 'https://example.com:8443/', actual: (string) $newUri);
    }

    public function test_it_returns_null_port_when_default_port_set() : void
    {
        // Arrange
        $uri = Uri::fromString(uri: 'https://example.com');

        // Act
        $newUri = $uri->withPort(port: 443); // default https port

        // Assert
        self::assertNull(actual: $newUri->getPort());
    }

    // ========== REGRESSION: Previously failing scenarios ==========

    public function test_it_does_not_lose_fragment_when_building_uri() : void
    {
        // Arrange
        $uriString = 'https://example.com/path#section';

        // Act
        $uri = Uri::fromString(uri: $uriString);

        // Assert
        self::assertStringContainsString(needle: '#section', haystack: (string) $uri);
        self::assertSame(expected: 'section', actual: $uri->getFragment());
    }

    public function test_it_preserves_user_info_through_mutations() : void
    {
        // Arrange
        $uriString = 'https://user:pass@example.com/old';

        // Act
        $uri    = Uri::fromString(uri: $uriString);
        $newUri = $uri->withPath(path: '/new');

        // Assert
        self::assertSame(expected: 'user:pass', actual: $newUri->getUserInfo());
        self::assertSame(expected: 'https://user:pass@example.com/new', actual: (string) $newUri);
    }

    public function test_it_round_trips_complex_uri() : void
    {
        // Arrange
        $original = 'https://user:pass@example.com:8080/path?foo=bar&baz=qux#section';

        // Act
        $uri = Uri::fromString(uri: $original);
        $roundTrip = (string) $uri;

        // Assert
        self::assertSame(expected: $original, actual: $roundTrip);
    }
}