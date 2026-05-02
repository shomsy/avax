<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Logging;

use Avax\Components\Operations\Logging\System\Capabilities\Redaction\SecretRedactor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SecretRedactor::class)]
final class SecretRedactorTest extends TestCase
{
    public static function sensitiveKeysProvider(): array
    {
        return [
            ['password'], ['passwd'], ['pass'], ['pwd'], ['secret'],
            ['secret_key'], ['api_key'], ['apikey'], ['api-key'],
            ['access_token'], ['access-token'], ['refresh_token'], ['refresh-token'],
            ['auth_token'], ['auth-token'], ['authorization'], ['token'],
            ['bearer'], ['jwt'], ['private_key'], ['private-key'],
            ['credit_card'], ['creditcard'], ['card_number'], ['cardnumber'],
            ['cvv'], ['cvc'], ['card_cvv'], ['ssn'], ['social_security'],
            ['database_password'], ['db_password'], ['db-pass'],
            ['encryption_key'], ['encryption-key'],
        ];
    }

    public static function nonSensitiveKeysProvider(): array
    {
        return [
            ['name'], ['email'], ['username'], ['user_id'],
            ['address'], ['phone'], ['city'], ['country'],
            ['description'], ['title'],
        ];
    }

    #[Test]
    public function password_redaction_by_key_name() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['password' => 'supersecret123', 'username' => 'admin']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['password']);
        self::assertSame('admin', $result['username']);
    }

    #[Test]
    public function passwd_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['passwd' => 'mypassword', 'name' => 'John']);
        self::assertSame('[PASSWD: ***REDACTED***]', $result['passwd']);
    }

    #[Test]
    public function pass_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['pass' => 'shortpass', 'email' => 'john@example.com']);
        self::assertSame('[PASS: ***REDACTED***]', $result['pass']);
    }

    #[Test]
    public function pwd_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['pwd' => 'pwdvalue', 'other' => 'safe']);
        self::assertSame('[PWD: ***REDACTED***]', $result['pwd']);
        self::assertSame('safe', $result['other']);
    }

    #[Test]
    public function secret_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['secret' => 'mysecret', 'public' => 'data']);
        self::assertSame('[SECRET: ***REDACTED***]', $result['secret']);
        self::assertSame('data', $result['public']);
    }

    #[Test]
    public function bearer_token_redaction_in_string() : void
    {
        $redactor = new SecretRedactor();
        $input    = 'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9';
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[BEARER_TOKEN:', $result);
        self::assertStringNotContainsString('Bearer eyJhbGci', $result);
    }

    #[Test]
    public function simple_bearer_token_redaction() : void
    {
        $redactor = new SecretRedactor();
        $input    = 'Bearer abc123token';
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[BEARER_TOKEN:', $result);
        self::assertStringNotContainsString('abc123token', $result);
    }

    #[Test]
    public function access_token_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['access_token' => 'token-value-123', 'scope' => 'read:write']);
        self::assertSame('[ACCESS_TOKEN: ***REDACTED***]', $result['access_token']);
        self::assertSame('read:write', $result['scope']);
    }

    #[Test]
    public function refresh_token_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['refresh_token' => 'refresh-value-456', 'expires_in' => 3600]);
        self::assertSame('[REFRESH_TOKEN: ***REDACTED***]', $result['refresh_token']);
        self::assertSame(3600, $result['expires_in']);
    }

    #[Test]
    public function auth_token_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['auth_token' => 'auth-value-789', 'user' => 'admin']);
        self::assertSame('[AUTH_TOKEN: ***REDACTED***]', $result['auth_token']);
        self::assertSame('admin', $result['user']);
    }

    #[Test]
    public function token_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['token' => 'my-token-value', 'name' => 'Test']);
        self::assertSame('[TOKEN: ***REDACTED***]', $result['token']);
        self::assertSame('Test', $result['name']);
    }

    #[Test]
    public function authorization_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['authorization' => 'Bearer xyz', 'method' => 'GET']);
        self::assertSame('[AUTHORIZATION: ***REDACTED***]', $result['authorization']);
        self::assertSame('GET', $result['method']);
    }

    #[Test]
    public function jwt_token_redaction_in_string() : void
    {
        $redactor = new SecretRedactor();
        $jwt      = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.dozjgNryP4J3jVmNHl0w5N_XgL0n3I9PlFUP0THsR8U';
        $result   = $redactor->redactString($jwt);
        self::assertStringContainsString('[JWT_TOKEN:', $result);
        self::assertStringNotContainsString('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9', $result);
    }

    #[Test]
    public function jwt_token_in_context() : void
    {
        $redactor = new SecretRedactor();
        $jwt      = 'eyJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiYWRtaW4ifQ.signature123';
        $result   = $redactor->redactArray(['jwt' => $jwt, 'user' => 'admin']);
        self::assertSame('[JWT: ***REDACTED***]', $result['jwt']);
    }

    #[Test]
    public function jwt_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['jwt' => 'jwt-token-value', 'role' => 'user']);
        self::assertSame('[JWT: ***REDACTED***]', $result['jwt']);
        self::assertSame('user', $result['role']);
    }

    #[Test]
    public function api_key_upper_case_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['API_KEY' => 'key-12345', 'service' => 'external-api']);
        self::assertSame('[API_KEY: ***REDACTED***]', $result['API_KEY']);
        self::assertSame('external-api', $result['service']);
    }

    #[Test]
    public function api_key_lower_case_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['api_key' => 'key-67890', 'service' => 'another-api']);
        self::assertSame('[API_KEY: ***REDACTED***]', $result['api_key']);
    }

    #[Test]
    public function apikey_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['apikey' => 'singleword123', 'version' => 'v2']);
        self::assertSame('[APIKEY: ***REDACTED***]', $result['apikey']);
        self::assertSame('v2', $result['version']);
    }

    #[Test]
    public function api_dash_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['api-key' => 'dashed-key-value', 'enabled' => true]);
        self::assertSame('[API-KEY: ***REDACTED***]', $result['api-key']);
    }

    #[Test]
    public function generic_api_key_pattern_in_string() : void
    {
        $redactor = new SecretRedactor();
        $input    = 'config: api_key=abcdefghijklmnop1234';
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[GENERIC_API_KEY:', $result);
        self::assertStringNotContainsString('abcdefghijklmnop1234', $result);
    }

    #[Test]
    public function aws_secret_access_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['AWS_SECRET_ACCESS_KEY' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY', 'region' => 'us-east-1']);
        self::assertSame('[AWS_SECRET_ACCESS_KEY: ***REDACTED***]', $result['AWS_SECRET_ACCESS_KEY']);
        self::assertSame('us-east-1', $result['region']);
    }

    #[Test]
    public function aws_access_key_pattern_in_string() : void
    {
        $redactor = new SecretRedactor();
        $input    = 'Using key AKIAIOSFODNN7EXAMPLE for access';
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[AWS_ACCESS_KEY:', $result);
        self::assertStringNotContainsString('AKIAIOSFODNN7EXAMPLE', $result);
    }

    #[Test]
    public function secret_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['secret_key' => 'my-secret-key-value', 'algorithm' => 'sha256']);
        self::assertSame('[SECRET_KEY: ***REDACTED***]', $result['secret_key']);
        self::assertSame('sha256', $result['algorithm']);
    }

    #[Test]
    public function visa_credit_card_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactString('4111 1111 1111 1111');
        self::assertStringContainsString('[CREDIT_CARD:', $result);
        self::assertStringNotContainsString('4111', $result);
    }

    #[Test]
    public function master_card_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactString('5500 0000 0000 0004');
        self::assertStringContainsString('[CREDIT_CARD:', $result);
        self::assertStringNotContainsString('5500', $result);
    }

    #[Test]
    public function amex_credit_card_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactString('3782 822463 10005');
        self::assertStringContainsString('[CREDIT_CARD:', $result);
        self::assertStringNotContainsString('3782', $result);
    }

    #[Test]
    public function credit_card_with_dashes() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactString('4111-1111-1111-1111');
        self::assertStringContainsString('[CREDIT_CARD:', $result);
        self::assertStringNotContainsString('4111-1111', $result);
    }

    #[Test]
    public function credit_card_without_separators() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactString('4111111111111111');
        self::assertStringContainsString('[CREDIT_CARD:', $result);
        self::assertStringNotContainsString('4111111111111111', $result);
    }

    #[Test]
    public function credit_card_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['credit_card' => '4111111111111111', 'expiry' => '12/25']);
        self::assertSame('[CREDIT_CARD: ***REDACTED***]', $result['credit_card']);
        self::assertSame('12/25', $result['expiry']);
    }

    #[Test]
    public function card_number_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['card_number' => '5500000000000004', 'cvv' => '123']);
        self::assertSame('[CARD_NUMBER: ***REDACTED***]', $result['card_number']);
        self::assertSame('[CVV: ***REDACTED***]', $result['cvv']);
    }

    #[Test]
    public function cardnumber_concat_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['cardnumber' => '4111111111111111', 'name' => 'John Doe']);
        self::assertSame('[CARDNUMBER: ***REDACTED***]', $result['cardnumber']);
        self::assertSame('John Doe', $result['name']);
    }

    #[Test]
    public function ssn_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactString('123-45-6789');
        self::assertStringContainsString('[SSN:', $result);
        self::assertStringNotContainsString('123-45-6789', $result);
    }

    #[Test]
    public function ssn_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['ssn' => '987-65-4321', 'name' => 'Jane Doe']);
        self::assertSame('[SSN: ***REDACTED***]', $result['ssn']);
        self::assertSame('Jane Doe', $result['name']);
    }

    #[Test]
    public function ssn_pattern_in_mixed_string() : void
    {
        $redactor = new SecretRedactor();
        $input    = 'User SSN: 111-22-3333, Name: John';
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[SSN:', $result);
        self::assertStringContainsString('Name: John', $result);
        self::assertStringNotContainsString('111-22-3333', $result);
    }

    #[Test]
    public function social_security_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['social_security' => '123-45-6789', 'dob' => '1990-01-01']);
        self::assertSame('[SOCIAL_SECURITY: ***REDACTED***]', $result['social_security']);
        self::assertSame('1990-01-01', $result['dob']);
    }

    #[Test]
    public function private_key_block_redaction() : void
    {
        $redactor = new SecretRedactor();
        $input    = "-----BEGIN RSA PRIVATE KEY-----\nMIIEowIBAAKCAQEA...\n-----END RSA PRIVATE KEY-----";
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[PRIVATE_KEY_BLOCK:', $result);
        self::assertStringNotContainsString('BEGIN RSA PRIVATE KEY', $result);
    }

    #[Test]
    public function ec_private_key_block_redaction() : void
    {
        $redactor = new SecretRedactor();
        $input    = '-----BEGIN EC PRIVATE KEY-----';
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[PRIVATE_KEY_BLOCK:', $result);
        self::assertStringNotContainsString('BEGIN EC PRIVATE KEY', $result);
    }

    #[Test]
    public function dsa_private_key_block_redaction() : void
    {
        $redactor = new SecretRedactor();
        $input    = '-----BEGIN DSA PRIVATE KEY-----';
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[PRIVATE_KEY_BLOCK:', $result);
    }

    #[Test]
    public function simple_private_key_block_redaction() : void
    {
        $redactor = new SecretRedactor();
        $input    = '-----BEGIN PRIVATE KEY-----';
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[PRIVATE_KEY_BLOCK:', $result);
    }

    #[Test]
    public function private_key_redaction_by_key() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['private_key' => '-----BEGIN RSA PRIVATE KEY-----', 'certificate' => 'public-cert']);
        self::assertSame('[PRIVATE_KEY: ***REDACTED***]', $result['private_key']);
        self::assertSame('public-cert', $result['certificate']);
    }

    #[Test]
    public function custom_sensitive_keys() : void
    {
        $redactor = new SecretRedactor(additionalSensitiveKeys: ['my_custom_secret', 'api_token']);
        $result = $redactor->redactArray(['my_custom_secret' => 'custom-value', 'api_token' => 'token-value', 'public' => 'safe-value']);
        self::assertSame('[MY_CUSTOM_SECRET: ***REDACTED***]', $result['my_custom_secret']);
        self::assertSame('[API_TOKEN: ***REDACTED***]', $result['api_token']);
        self::assertSame('safe-value', $result['public']);
    }

    #[Test]
    public function custom_sensitive_keys_case_insensitive() : void
    {
        $redactor = new SecretRedactor(additionalSensitiveKeys: ['MySecret']);
        $result = $redactor->redactArray(['mysecret' => 'value1', 'MYSECRET' => 'value2']);
        self::assertSame('[MYSECRET: ***REDACTED***]', $result['mysecret']);
        self::assertSame('[MYSECRET: ***REDACTED***]', $result['MYSECRET']);
    }

    #[Test]
    public function nested_arrays_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray([
                                               'user' => ['name' => 'John', 'password' => 'secret123', 'profile' => ['email' => 'john@example.com', 'api_key' => 'key-12345']],
            'metadata' => ['version' => '1.0'],
        ]);
        self::assertSame('John', $result['user']['name']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['user']['password']);
        self::assertSame('john@example.com', $result['user']['profile']['email']);
        self::assertSame('[API_KEY: ***REDACTED***]', $result['user']['profile']['api_key']);
        self::assertSame('1.0', $result['metadata']['version']);
    }

    #[Test]
    public function deeply_nested_array_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['level1' => ['level2' => ['level3' => ['password' => 'deep-secret', 'value' => 'safe']]]]);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['level1']['level2']['level3']['password']);
        self::assertSame('safe', $result['level1']['level2']['level3']['value']);
    }

    #[Test]
    public function nested_array_with_string_values_containing_patterns() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['request' => ['headers' => ['Authorization' => 'Bearer token123', 'Content-Type' => 'application/json']]]);
        self::assertSame('[AUTHORIZATION: ***REDACTED***]', $result['request']['headers']['Authorization']);
        self::assertSame('application/json', $result['request']['headers']['Content-Type']);
    }

    #[Test]
    public function non_sensitive_data_passthrough() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['name' => 'John Doe', 'age' => 30, 'email' => 'john@example.com', 'active' => true, 'score' => 95.5, 'tags' => ['php', 'test']]);
        self::assertSame('John Doe', $result['name']);
        self::assertSame(30, $result['age']);
        self::assertSame('john@example.com', $result['email']);
        self::assertSame(true, $result['active']);
        self::assertSame(95.5, $result['score']);
        self::assertSame(['php', 'test'], $result['tags']);
    }

    #[Test]
    public function safe_string_passthrough() : void
    {
        $redactor = new SecretRedactor();
        $input    = 'Hello, World! This is a safe string.';
        self::assertSame($input, $redactor->redactString($input));
    }

    #[Test]
    public function numeric_string_passthrough() : void
    {
        $redactor = new SecretRedactor();
        self::assertSame('12345', $redactor->redactString('12345'));
    }

    #[Test]
    public function empty_value_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['password' => '', 'username' => 'admin']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['password']);
        self::assertSame('admin', $result['username']);
    }

    #[Test]
    public function null_value_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['password' => null, 'name' => 'John']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['password']);
        self::assertSame('John', $result['name']);
    }

    #[Test]
    public function empty_array() : void
    {
        $redactor = new SecretRedactor();
        self::assertSame([], $redactor->redactArray([]));
    }

    #[Test]
    public function empty_string() : void
    {
        $redactor = new SecretRedactor();
        self::assertSame('', $redactor->redactString(''));
    }

    #[Test]
    public function mixed_sensitive_and_non_sensitive() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['username' => 'admin', 'password' => 'secret', 'email' => 'admin@test.com', 'token' => 'bearer-token', 'role' => 'admin', 'ssn' => '123-45-6789']);
        self::assertSame('admin', $result['username']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['password']);
        self::assertSame('admin@test.com', $result['email']);
        self::assertSame('[TOKEN: ***REDACTED***]', $result['token']);
        self::assertSame('admin', $result['role']);
        self::assertSame('[SSN: ***REDACTED***]', $result['ssn']);
    }

    #[Test]
    public function custom_redaction_mask() : void
    {
        $redactor = new SecretRedactor(redactionMask: '[HIDDEN]');
        $result = $redactor->redactArray(['password' => 'secret']);
        self::assertSame('[PASSWORD: [HIDDEN]]', $result['password']);
    }

    #[Test]
    public function bearer_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['bearer' => 'bearer-token-value', 'type' => 'oauth']);
        self::assertSame('[BEARER: ***REDACTED***]', $result['bearer']);
        self::assertSame('oauth', $result['type']);
    }

    #[Test]
    public function database_password_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['database_password' => 'db-pass-123', 'host' => 'localhost']);
        self::assertSame('[DATABASE_PASSWORD: ***REDACTED***]', $result['database_password']);
        self::assertSame('localhost', $result['host']);
    }

    #[Test]
    public function db_password_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['db_password' => 'db-pass-456', 'driver' => 'mysql']);
        self::assertSame('[DB_PASSWORD: ***REDACTED***]', $result['db_password']);
        self::assertSame('mysql', $result['driver']);
    }

    #[Test]
    public function encryption_key_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['encryption_key' => 'enc-key-789', 'algorithm' => 'aes-256']);
        self::assertSame('[ENCRYPTION_KEY: ***REDACTED***]', $result['encryption_key']);
        self::assertSame('aes-256', $result['algorithm']);
    }

    #[Test]
    public function cvv_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['cvv' => '123', 'card_number' => '4111111111111111']);
        self::assertSame('[CVV: ***REDACTED***]', $result['cvv']);
        self::assertSame('[CARD_NUMBER: ***REDACTED***]', $result['card_number']);
    }

    #[Test]
    public function cvc_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['cvc' => '456', 'expiry' => '12/25']);
        self::assertSame('[CVC: ***REDACTED***]', $result['cvc']);
        self::assertSame('12/25', $result['expiry']);
    }

    #[Test]
    public function card_cvv_redaction() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['card_cvv' => '789', 'name' => 'Cardholder']);
        self::assertSame('[CARD_CVV: ***REDACTED***]', $result['card_cvv']);
        self::assertSame('Cardholder', $result['name']);
    }

    #[Test]
    public function authorization_header_pattern_redaction() : void
    {
        $redactor = new SecretRedactor();
        $input    = 'Authorization: Basic dXNlcjpwYXNz';
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[AUTHORIZATION_HEADER:', $result);
        self::assertStringNotContainsString('dXNlcjpwYXNz', $result);
    }

    #[Test]
    public function api_key_header_pattern_redaction() : void
    {
        $redactor = new SecretRedactor();
        $input    = 'x-api-key: my-api-key-value-123';
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[API_KEY_HEADER:', $result);
    }

    #[Test]
    public function multiple_patterns_in_string() : void
    {
        $redactor = new SecretRedactor();
        $input    = 'SSN: 123-45-6789, CC: 4111111111111111, Key: AKIAIOSFODNN7EXAMPLE';
        $result   = $redactor->redactString($input);
        self::assertStringContainsString('[SSN:', $result);
        self::assertStringContainsString('[CREDIT_CARD:', $result);
        self::assertStringContainsString('[AWS_ACCESS_KEY:', $result);
        self::assertStringNotContainsString('123-45-6789', $result);
        self::assertStringNotContainsString('4111111111111111', $result);
        self::assertStringNotContainsString('AKIAIOSFODNN7EXAMPLE', $result);
    }

    #[Test]
    public function case_insensitive_sensitive_key_matching() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['PASSWORD' => 'upper', 'Password' => 'mixed', 'password' => 'lower']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['PASSWORD']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['Password']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['password']);
    }

    #[Test]
    public function underscored_and_dashed_key_normalization() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['api_key' => 'value1', 'api-key' => 'value2', 'API_KEY' => 'value3']);
        self::assertSame('[API_KEY: ***REDACTED***]', $result['api_key']);
        self::assertSame('[API-KEY: ***REDACTED***]', $result['api-key']);
        self::assertSame('[API_KEY: ***REDACTED***]', $result['API_KEY']);
    }

    #[Test]
    public function partial_match_sensitive_key_pattern() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['user_password_hash' => 'hash-value', 'password_reset_token' => 'reset-token']);
        self::assertSame('[USER_PASSWORD_HASH: ***REDACTED***]', $result['user_password_hash']);
        self::assertSame('[PASSWORD_RESET_TOKEN: ***REDACTED***]', $result['password_reset_token']);
    }

    #[Test]
    public function credential_pattern_matching() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['user_credential' => 'cred-value', 'credentials' => 'creds-value']);
        self::assertSame('[USER_CREDENTIAL: ***REDACTED***]', $result['user_credential']);
        self::assertSame('[CREDENTIALS: ***REDACTED***]', $result['credentials']);
    }

    #[Test]
    public function redactor_with_empty_additional_keys() : void
    {
        $redactor = new SecretRedactor(additionalSensitiveKeys: []);
        $result = $redactor->redactArray(['password' => 'secret', 'safe' => 'value']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['password']);
        self::assertSame('value', $result['safe']);
    }

    #[Test]
    public function string_with_no_patterns() : void
    {
        $redactor = new SecretRedactor();
        $input    = 'This is a normal log message with no secrets.';
        self::assertSame($input, $redactor->redactString($input));
    }

    #[Test]
    public function array_with_integer_keys() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray([0 => 'value0', 1 => 'value1', 'password' => 'secret']);
        self::assertSame('value0', $result[0]);
        self::assertSame('value1', $result[1]);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['password']);
    }

    #[Test]
    public function boolean_values_in_array() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['is_admin' => true, 'is_active' => false, 'token' => 'secret']);
        self::assertSame(true, $result['is_admin']);
        self::assertSame(false, $result['is_active']);
        self::assertSame('[TOKEN: ***REDACTED***]', $result['token']);
    }

    #[Test]
    public function zero_values_in_array() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['count' => 0, 'balance' => 0.0, 'password' => 'secret']);
        self::assertSame(0, $result['count']);
        self::assertSame(0.0, $result['balance']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['password']);
    }

    #[Test]
    public function complex_nested_structure() : void
    {
        $redactor = new SecretRedactor();
        $input    = [
            'users' => [
                ['name' => 'Alice', 'password' => 'alice-pass', 'api_key' => 'alice-key'],
                ['name' => 'Bob', 'password' => 'bob-pass', 'ssn' => '111-22-3333'],
            ],
            'settings' => ['debug' => false, 'secret' => 'app-secret'],
        ];
        $result = $redactor->redactArray($input);
        self::assertSame('Alice', $result['users'][0]['name']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['users'][0]['password']);
        self::assertSame('[API_KEY: ***REDACTED***]', $result['users'][0]['api_key']);
        self::assertSame('Bob', $result['users'][1]['name']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['users'][1]['password']);
        self::assertSame('[SSN: ***REDACTED***]', $result['users'][1]['ssn']);
        self::assertSame(false, $result['settings']['debug']);
        self::assertSame('[SECRET: ***REDACTED***]', $result['settings']['secret']);
    }

    #[Test]
    public function redact_array_returns_new_array() : void
    {
        $redactor = new SecretRedactor();
        $original = ['password' => 'secret', 'name' => 'John'];
        $result   = $redactor->redactArray($original);
        self::assertSame('secret', $original['password']);
        self::assertSame('John', $original['name']);
        self::assertSame('[PASSWORD: ***REDACTED***]', $result['password']);
    }

    #[Test]
    public function redact_string_returns_new_string() : void
    {
        $redactor = new SecretRedactor();
        $original = 'SSN: 123-45-6789';
        $result   = $redactor->redactString($original);
        self::assertSame('SSN: 123-45-6789', $original);
        self::assertStringContainsString('[SSN:', $result);
    }

    #[Test]
    public function redactor_is_readonly() : void
    {
        self::assertInstanceOf(SecretRedactor::class, new SecretRedactor());
    }

    #[DataProvider('sensitiveKeysProvider')]
    #[Test]
    public function all_sensitive_keys_are_redacted(string $key) : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray([$key => 'sensitive-value', 'safe' => 'safe-value']);
        self::assertStringContainsString('REDACTED', $result[$key]);
        self::assertSame('safe-value', $result['safe']);
    }

    #[DataProvider('nonSensitiveKeysProvider')]
    #[Test]
    public function non_sensitive_keys_are_not_redacted(string $key) : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray([$key => 'safe-value']);
        self::assertSame('safe-value', $result[$key]);
    }
}
