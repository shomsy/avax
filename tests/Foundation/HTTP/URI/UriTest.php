<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI;

use Avax\HTTP\URI\Uri;
use Avax\HTTP\URI\Parts\Query;
use PHPUnit\Framework\TestCase;

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
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('https', $uri->getScheme());
        self::assertSame('user:pass', $uri->getUserInfo());
        self::assertSame('example.com', $uri->getHost());
        self::assertSame(8080, $uri->getPort());
        self::assertSame('/path', $uri->getPath());
        self::assertSame('query=value', $uri->getQuery());
        self::assertSame('fragment', $uri->getFragment());
        self::assertSame('https://user:pass@example.com:8080/path?query=value#fragment', (string) $uri);
    }

    public function test_it_parses_uri_without_port_when_using_default_port() : void
    {
        // Arrange
        $httpsUri = 'https://example.com:443/api';
        $httpUri  = 'http://example.com:80/api';

        // Act & Assert
        $https = Uri::fromString($httpsUri);
        self::assertNull($https->getPort());
        self::assertSame('https://example.com/api', (string) $https);

        $http = Uri::fromString($httpUri);
        self::assertNull($http->getPort());
        self::assertSame('http://example.com/api', (string) $http);
    }

    public function test_it_parses_uri_with_non_default_port_when_specified() : void
    {
        // Arrange
        $uriString = 'https://example.com:8443/secure';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame(8443, $uri->getPort());
        self::assertSame('https://example.com:8443/secure', (string) $uri);
    }

    public function test_it_parses_uri_with_multiple_query_parameters() : void
    {
        // Arrange
        $uriString = 'https://example.com?foo=bar&baz=qux&alpha=beta';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('foo=bar&baz=qux&alpha=beta', $uri->getQuery());
        self::assertSame('https://example.com/?foo=bar&baz=qux&alpha=beta', (string) $uri);
    }

    public function test_it_normalizes_host_to_lowercase() : void
    {
        // Arrange
        $uriString = 'https://EXAMPLE.COM';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('example.com', $uri->getHost());
        self::assertSame('https://example.com/', (string) $uri);
    }

    public function test_it_normalizes_path_segments() : void
    {
        // Arrange: Path with . and .. should be normalized
        $uriString = 'https://example.com/./path/../other';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('/other', $uri->getPath());
        self::assertSame('https://example.com/other', (string) $uri);
    }

    public function test_it_encodes_path_segments() : void
    {
        // Arrange
        $uriString = 'https://example.com/user space/file.txt';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert - space should be encoded
        self::assertStringContainsString('user%20space', (string) $uri);
    }

    // ========== HAPPY PATH: URIs with optional components ==========

    public function test_it_parses_uri_without_user_info() : void
    {
        // Arrange
        $uriString = 'https://example.com/api';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('', $uri->getUserInfo());
        self::assertSame('https://example.com/api', (string) $uri);
    }

    public function test_it_parses_uri_without_port() : void
    {
        // Arrange
        $uriString = 'https://example.com/api';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertNull($uri->getPort());
        self::assertSame('https://example.com/api', (string) $uri);
    }

    public function test_it_parses_uri_without_query() : void
    {
        // Arrange
        $uriString = 'https://example.com/path';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('', $uri->getQuery());
        self::assertSame('https://example.com/path', (string) $uri);
    }

    public function test_it_parses_uri_without_fragment() : void
    {
        // Arrange
        $uriString = 'https://example.com/path';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('', $uri->getFragment());
        self::assertSame('https://example.com/path', (string) $uri);
    }

    public function test_it_parses_uri_with_only_user_without_password() : void
    {
        // Arrange
        $uriString = 'https://user@example.com';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('user', $uri->getUserInfo());
        self::assertSame('https://user@example.com/', (string) $uri);
    }

    // ========== HAPPY PATH: Relative and minimal URIs ==========

    public function test_it_parses_relative_path_only() : void
    {
        // Arrange
        $uriString = '/api/users';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('', $uri->getScheme());
        self::assertSame('', $uri->getHost());
        self::assertSame('/api/users', $uri->getPath());
        self::assertSame('/api/users', (string) $uri);
    }

    public function test_it_parses_path_with_query_no_scheme() : void
    {
        // Arrange
        $uriString = '/search?q=test';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('/search', $uri->getPath());
        self::assertSame('q=test', $uri->getQuery());
    }

    // ========== IMMUTABILITY: with* methods return new instances ==========

    public function test_it_returns_new_instance_when_withScheme_called() : void
    {
        // Arrange
        $originalUri = Uri::fromString('http://example.com');

        // Act
        $newUri = $originalUri->withScheme('https');

        // Assert
        self::assertNotSame($originalUri, $newUri);
        self::assertSame('http', $originalUri->getScheme());
        self::assertSame('https', $newUri->getScheme());
    }

    public function test_it_returns_new_instance_when_withHost_called() : void
    {
        // Arrange
        $originalUri = Uri::fromString('https://example.com');

        // Act
        $newUri = $originalUri->withHost('new.com');

        // Assert
        self::assertNotSame($originalUri, $newUri);
        self::assertSame('example.com', $originalUri->getHost());
        self::assertSame('new.com', $newUri->getHost());
    }

    public function test_it_returns_new_instance_when_withPath_called() : void
    {
        // Arrange
        $originalUri = Uri::fromString('https://example.com/old');

        // Act
        $newUri = $originalUri->withPath('/new');

        // Assert
        self::assertNotSame($originalUri, $newUri);
        self::assertSame('/old', $originalUri->getPath());
        self::assertSame('/new', $newUri->getPath());
    }

    public function test_it_returns_new_instance_when_withQuery_called() : void
    {
        // Arrange
        $originalUri = Uri::fromString('https://example.com?old=value');

        // Act
        $newUri = $originalUri->withQuery('new=value');

        // Assert
        self::assertNotSame($originalUri, $newUri);
        self::assertSame('old=value', $originalUri->getQuery());
        self::assertSame('new=value', $newUri->getQuery());
    }

    public function test_it_returns_new_instance_when_withFragment_called() : void
    {
        // Arrange
        $originalUri = Uri::fromString('https://example.com#old');

        // Act
        $newUri = $originalUri->withFragment('new');

        // Assert
        self::assertNotSame($originalUri, $newUri);
        self::assertSame('old', $originalUri->getFragment());
        self::assertSame('new', $newUri->getFragment());
    }

    public function test_it_chains_multiple_with_methods() : void
    {
        // Arrange
        $originalUri = Uri::fromString('http://example.com/old?old=val#old');

        // Act
        $newUri = $originalUri
            ->withScheme('https')
            ->withHost('new.com')
            ->withPath('/new')
            ->withQuery('new=val')
            ->withFragment('new');

        // Assert
        self::assertSame('https://new.com/new?new=val#new', (string) $newUri);
        // Original unchanged
        self::assertSame('http://example.com/old?old=val#old', (string) $originalUri);
    }

    // ========== FAILURE: Invalid schemes ==========

    public function test_it_throws_when_scheme_is_invalid() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        Uri::fromString('invalid://example.com');
    }

    public function test_it_throws_when_withScheme_given_invalid_scheme() : void
    {
        // Arrange
        $uri = Uri::fromString('https://example.com');

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $uri->withScheme('invalid-protocol');
    }

    // ========== FAILURE: Invalid hosts ==========

    public function test_it_throws_when_host_is_invalid() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        Uri::fromString('https://invalid..host');
    }

    public function test_it_throws_when_withHost_given_empty_string() : void
    {
        // Arrange
        $uri = Uri::fromString('https://example.com');

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $uri->withHost('');
    }

    // ========== FAILURE: Invalid ports ==========

    public function test_it_throws_when_port_exceeds_maximum() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        Uri::fromString('https://example.com:99999');
    }

    public function test_it_throws_when_port_is_zero() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        Uri::fromString('https://example.com:0');
    }

    // ========== EDGE CASES: Empty and minimal values ==========

    public function test_it_handles_empty_query_string() : void
    {
        // Arrange
        $uriString = 'https://example.com?';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('', $uri->getQuery());
    }

    public function test_it_handles_path_with_trailing_slash() : void
    {
        // Arrange
        $uriString = 'https://example.com/';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('/', $uri->getPath());
        self::assertSame('https://example.com/', (string) $uri);
    }

    public function test_it_handles_root_path() : void
    {
        // Arrange
        $uriString = 'https://example.com';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('/', $uri->getPath());
    }

    public function test_it_preserves_empty_path_segment() : void
    {
        // Arrange
        $uriString = 'https://example.com/api//users';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert - leading slash preserved, empty segments removed
        self::assertSame('/api/users', $uri->getPath());
    }

    public function test_it_handles_query_with_empty_value() : void
    {
        // Arrange
        $uriString = 'https://example.com?key=';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertSame('key=', $uri->getQuery());
    }

    // ========== EDGE CASES: Special characters in components ==========

    public function test_it_encodes_special_characters_in_path() : void
    {
        // Arrange
        $uriString = 'https://example.com/path with spaces';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertStringContainsString('%20', (string) $uri);
    }

    public function test_it_double_encodes_percent_sequences() : void
    {
        // Arrange
        $uriString = 'https://example.com/path%20with%20spaces';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        // Percent signs get encoded again
        $rendered = (string) $uri;
        self::assertStringContainsString('%25', $rendered);
    }

    // ========== PSR-7 INTERFACE COMPATIBILITY ==========

    public function test_it_implements_psr7_uri_interface() : void
    {
        // Arrange & Act
        $uri = Uri::fromString('https://example.com');

        // Assert
        self::assertInstanceOf(\Psr\Http\Message\UriInterface::class, $uri);
    }

    public function test_it_supports_psr7_withUserInfo_method() : void
    {
        // Arrange
        $uri = Uri::fromString('https://example.com');

        // Act
        $newUri = $uri->withUserInfo('admin', 'secret');

        // Assert
        self::assertSame('admin:secret', $newUri->getUserInfo());
        self::assertSame('https://admin:secret@example.com/', (string) $newUri);
    }

    public function test_it_supports_psr7_withPort_method() : void
    {
        // Arrange
        $uri = Uri::fromString('https://example.com');

        // Act
        $newUri = $uri->withPort(8443);

        // Assert
        self::assertSame(8443, $newUri->getPort());
        self::assertSame('https://example.com:8443/', (string) $newUri);
    }

    public function test_it_returns_null_port_when_default_port_set() : void
    {
        // Arrange
        $uri = Uri::fromString('https://example.com');

        // Act
        $newUri = $uri->withPort(443); // default https port

        // Assert
        self::assertNull($newUri->getPort());
    }

    // ========== REGRESSION: Previously failing scenarios ==========

    public function test_it_does_not_lose_fragment_when_building_uri() : void
    {
        // Arrange
        $uriString = 'https://example.com/path#section';

        // Act
        $uri = Uri::fromString($uriString);

        // Assert
        self::assertStringContainsString('#section', (string) $uri);
        self::assertSame('section', $uri->getFragment());
    }

    public function test_it_preserves_user_info_through_mutations() : void
    {
        // Arrange
        $uriString = 'https://user:pass@example.com/old';

        // Act
        $uri    = Uri::fromString($uriString);
        $newUri = $uri->withPath('/new');

        // Assert
        self::assertSame('user:pass', $newUri->getUserInfo());
        self::assertSame('https://user:pass@example.com/new', (string) $newUri);
    }

    public function test_it_round_trips_complex_uri() : void
    {
        // Arrange
        $original = 'https://user:pass@example.com:8080/path?foo=bar&baz=qux#section';

        // Act
        $uri       = Uri::fromString($original);
        $roundTrip = (string) $uri;

        // Assert
        self::assertSame($original, $roundTrip);
    }
}