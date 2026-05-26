<?php

declare(strict_types=1);

/**
 * check-shallow-tests.php
 *
 * Detects fake/useless/shallow tests that prove nothing meaningful.
 * Reports findings with severity: HIGH / MEDIUM / LOW.
 *
 * For each finding, outputs WHY the test is weak, what RISK it creates,
 * and what STRONGER alternative would look like.
 *
 * Exit codes: 0 = GREEN, 1 = findings exist
 */

$root = dirname(__DIR__, 2);
require_once $root . '/tooling/validation/governance-gate-baseline-lib.php';

$testsDir = $root . '/tests';
$findings = [];
$filesScanned = 0;
$shallowCount = 0;
$mode = avax_gate_mode($argv);
$baselinePath = $root . '/.agents/management/baselines/shallow-tests-baseline.json';
$writeBaselinePath = avax_gate_arg_value($argv, '--write-baseline');

/**
 * Analyze a test file for shallow patterns.
 *
 * Returns an array of findings, each with:
 *   - pattern: string identifier for the detection category
 *   - severity: HIGH | MEDIUM | LOW
 *   - why: specific explanation of why this test is weak
 *   - risk: what risk this creates (security gap, regression blind spot, etc.)
 *   - suggestion: what a stronger alternative would look like
 */
function analyzeTestFile(string $path): array
{
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    $findings = [];

    // Count assertions and test methods once for reuse
    $hasAssertion = (bool) preg_match('/\$this->assert|expect\(|self::assert/', $content);
    $hasTestMethod = (bool) preg_match('/public\s+function\s+test_/i', $content);
    $assertCount = preg_match_all('/\$this->assert|expect\(|self::assert/', $content);
    $isSecuritySensitive = (bool) preg_match('/Auth|Token|Access|Security|Permission|Tenant|Mfa|Passkey|Session|Credential/', $path);
    $lineCount = count($lines);

    // ------------------------------------------------------------------
    // Pattern 1: Meaningless assertions — assertTrue(true), assertFalse(false),
    //            assertNotNull on freshly created objects, assertInstanceOf alone
    // ------------------------------------------------------------------
    if (preg_match('/\$this->assertTrue\s*\(\s*true\s*\)/', $content)) {
        $findings[] = [
            'pattern' => 'meaningless_assertion',
            'why' => 'assertTrue(true) always passes regardless of system state. It proves the test runner works, not that any behavior is correct. This is equivalent to having no assertion at all.',
            'risk' => 'Creates fake GREEN. The test passes even if the underlying code is broken, removed, or returns wrong results. Regression blind spot: broken behavior will not be caught.',
            'suggestion' => 'Replace with a meaningful assertion that validates actual behavior. For example: assertTrue($result->isAuthenticated()) after attempting login with valid credentials, or assertTrue($validator->fails()) after submitting invalid input.',
        ];
    }

    if (preg_match('/\$this->assertFalse\s*\(\s*false\s*\)/', $content)) {
        $findings[] = [
            'pattern' => 'meaningless_assertion',
            'why' => 'assertFalse(false) always passes regardless of system state. It proves nothing about the code under test.',
            'risk' => 'Creates fake GREEN. Broken behavior, removed logic, or wrong return values will not be caught by this test.',
            'suggestion' => 'Replace with an assertion that validates actual behavior. For example: assertFalse($result->isAuthorized()) after attempting access without permission.',
        ];
    }

    // assertNotNull on a freshly created object (new X followed by assertNotNull on it)
    if (preg_match('/\\$\w+\s*=\s*new\s+\w+.*\$this->assertNotNull\s*\(\s*\\$\w+\s*\)/s', $content)) {
        $findings[] = [
            'pattern' => 'meaningless_assertion',
            'why' => 'assertNotNull on a freshly instantiated object proves only that the constructor did not throw. Since the object was just created by the test itself, assertNotNull is guaranteed to pass if construction succeeded. It adds no behavioral proof.',
            'risk' => 'Tests that construction does not throw are already covered by the constructor-only test pattern. An extra assertNotNull on a local variable creates fake assertion count inflation without testing any behavior.',
            'suggestion' => 'Remove the assertNotNull. If the constructor can throw, test the failure case with expectException. If the constructor succeeds, test actual behavior: call methods, verify outputs, check state transitions.',
        ];
    }

    // assertInstanceOf with no further assertions in the same method scope
    if (preg_match('/\$this->assertInstanceOf\s*\(/', $content)) {
        // Check if assertInstanceOf is the only assertion about a type
        $typeAsserts = preg_match_all('/\$this->assertInstanceOf\s*\(/', $content);
        $otherAsserts = preg_match_all('/\$this->assert(?!InstanceOf)[A-Z]/', $content);
        if ($typeAsserts > 0 && $otherAsserts === 0) {
            $findings[] = [
                'pattern' => 'meaningless_assertion',
                'why' => 'assertInstanceOf only verifies that an object is of a certain type. Without additional assertions, it proves nothing about the object\'s behavior, state, or correctness. A class can be the right type and still return wrong values, skip required side effects, or fail silently.',
                'risk' => 'Type-correct objects with broken behavior will pass. For example, an AuthTokenVerifier that implements the right interface but always returns true would pass an assertInstanceOf test.',
                'suggestion' => 'After assertInstanceOf, add behavioral assertions: verify the object returns correct values, performs expected side effects, rejects invalid input, and fails closed on errors. Example: $this->assertInstanceOf(TokenVerifier::class, $v); $this->assertFalse($v->verify(\'invalid-token\'));',
            ];
        }
    }

    // ------------------------------------------------------------------
    // Pattern 2: Fake coverage farming — tests that only check class exists,
    //            is instantiable, or constructor accepts parameters
    // ------------------------------------------------------------------
    if (preg_match('/class_exists\s*\(/i', $content) && !$hasAssertion) {
        $findings[] = [
            'pattern' => 'fake_coverage_farming',
            'why' => 'class_exists() only proves the class file can be autoloaded. It does not test any behavior, state transition, or business logic. A class that exists but returns wrong values or fails silently will pass this test.',
            'risk' => 'Coverage tools will count this file as "covered" even though no behavior was tested. Creates a false sense of completeness. Broken or deleted behavior inside the class will not be caught.',
            'suggestion' => 'Remove the class_exists check. Instead, instantiate the class and test its behavior: call methods with valid and invalid input, verify outputs, test error handling. Example: $result = $service->process($input); $this->assertTrue($result->isValid());',
        ];
    }

    // Tests that only instantiate without any behavioral assertions
    $instantiationOnly = preg_match_all('/\\$\w+\s*=\s*new\s+\w+/', $content);
    if ($hasTestMethod && !$hasAssertion && $instantiationOnly > 0) {
        $findings[] = [
            'pattern' => 'fake_coverage_farming',
            'why' => 'Test methods that only instantiate objects without any assertions prove nothing beyond "the constructor did not throw." This inflates coverage percentage without testing behavior, state transitions, error handling, or business logic.',
            'risk' => 'Coverage tools will report these lines as tested, creating fake confidence. If the class behavior changes, returns wrong values, or skips required operations, the test will not catch it. Security-sensitive classes with broken authorization or token validation will appear "tested."',
            'suggestion' => 'Add behavioral assertions after construction. Call methods with valid and invalid inputs. Verify outputs match expected values. Test error paths with expectException. For security classes, test denial paths: invalid tokens, expired sessions, unauthorized access.',
        ];
    }

    // ------------------------------------------------------------------
    // Pattern 3: Constructor-only tests (improved detection and messaging)
    // ------------------------------------------------------------------
    if ($hasTestMethod && !$hasAssertion && $instantiationOnly > 0) {
        // Only add if not already added as fake coverage farming with same message
        $alreadyAdded = false;
        foreach ($findings as $existing) {
            if ($existing['pattern'] === 'fake_coverage_farming' &&
                strpos($existing['why'], 'instantiate objects') !== false) {
                $alreadyAdded = true;
                break;
            }
        }
        if (!$alreadyAdded) {
            $findings[] = [
                'pattern' => 'constructor_only_test',
                'why' => 'A test that only constructs an object proves the constructor accepts the given parameters without throwing. It does not prove: the object behaves correctly, methods return expected values, state transitions work, invalid input is rejected, side effects occur, or error handling functions. Construction is a prerequisite for behavior, not a substitute for it.',
                'risk' => 'Constructor-only tests create the illusion of coverage while leaving all behavioral paths untested. A class can construct successfully but have completely broken methods, missing validation, or fail-open security behavior. In auth/security contexts, this means authorization bypasses or token validation failures will not be caught.',
                'suggestion' => 'After construction, test behavior in these categories: (1) happy path — valid input produces correct output, (2) failed-when — invalid input is rejected with appropriate errors, (3) validation-path — boundary values and edge cases are handled, (4) security-path — fail-closed behavior is proven, (5) lifecycle-path — reset, cleanup, and state transitions work correctly.',
            ];
        }
    }

    // ------------------------------------------------------------------
    // Pattern 4: Implementation-coupled tests (improved detection and messaging)
    // ------------------------------------------------------------------
    if (preg_match('/(invoke|setAccessible|Reflection|private.*method|getProperty|setAccessible)/i', $content)) {
        $findings[] = [
            'pattern' => 'implementation_coupled_test',
            'why' => 'Tests that use reflection, setAccessible, or invoke private methods couple the test to the internal implementation rather than the public contract. When implementation details change (which they should during refactoring), these tests break even if the public behavior is unchanged. This makes refactoring risky and expensive, discouraging necessary improvements.',
            'risk' => 'Two risks: (1) Brittle tests that break on harmless refactoring, causing test churn and developer frustration. (2) More dangerous: tests that pass because they bypass the public API and call internals directly, missing integration bugs where the public API composes multiple internals incorrectly. A test that calls a private method directly will never catch a bug in how the public method orchestrates that private method with others.',
            'suggestion' => 'Test through the public API only. If you cannot verify behavior through the public interface, either: (a) the public API is missing an observable output — add one, (b) the behavior belongs in a separate public class — extract it, (c) the method should be public, not private — change visibility. Example: instead of using reflection to verify internal state, assert the public method\'s return value or side effect.',
        ];
    }

    // ------------------------------------------------------------------
    // Pattern 5: No negative assertions — only happy path, no testExpectsException,
    //            no invalid input rejection
    // ------------------------------------------------------------------
    if ($hasTestMethod && $hasAssertion) {
        $hasNegativeTest = (bool) preg_match(
            '/(expectException|expectErrorMessage|fail\(|assertThrows|willThrowException|' .
            'invalid|denied|rejected|unauthorized|forbidden|fail.*when|must.*not|cannot|' .
            'test.*fail|test.*invalid|test.*denied|test.*reject|test.*error|' .
            'test.*unauthorized|test.*forbidden|test.*missing|test.*empty|' .
            'test.*null|test.*malformed|test.*expired)/i',
            $content
        );

        if (!$hasNegativeTest) {
            $findings[] = [
                'pattern' => 'no_negative_assertions',
                'why' => 'This test file only validates the happy path — what happens when everything goes right. It does not test what happens when input is invalid, permissions are missing, tokens are expired, dependencies fail, or edge cases occur. A system that works only on the happy path is not production-ready. Most bugs and security vulnerabilities live in the paths that handle unexpected or malicious input.',
                'risk' => 'Regression blind spot for denial behavior. If a future change accidentally removes input validation, authorization checks, or error handling, these tests will not catch it. For security-sensitive code, this means authorization bypasses, injection vulnerabilities, and fail-open behavior will not be detected by the test suite.',
                'suggestion' => 'Add negative tests for each security/behavioral boundary: (1) invalid input — test that malformed, empty, or out-of-range values are rejected, (2) unauthorized access — test that missing or insufficient permissions are denied, (3) expired/invalid tokens — test that stale credentials fail, (4) missing dependencies — test that null or absent required resources produce clear errors, (5) fail-closed — test that system errors result in denial, not silent success. Use expectException to verify the correct exception type and message.',
            ];
        }
    }

    // ------------------------------------------------------------------
    // Pattern 6: Only-happy-path auth tests (improved detection for security files)
    // ------------------------------------------------------------------
    if ($isSecuritySensitive && $hasTestMethod && $hasAssertion) {
        $hasAuthNegativeTest = (bool) preg_match(
            '/(test.*fail|test.*invalid|test.*denied|test.*reject|test.*unauthorized|' .
            'test.*forbidden|test.*expired|test.*malformed|test.*missing|test.*empty|' .
            'test.*revoked|test.*tampered|test.*replay|test.*bypass|' .
            'expectException|assert.*false.*auth|assert.*false.*token|' .
            'assert.*false.*access|assert.*false.*session|' .
            'fail.*auth|fail.*token|fail.*access|invalid.*cred|' .
            'denied|rejected|cannot|must not|should not|should fail)/i',
            $content
        );

        if (!$hasAuthNegativeTest) {
            $findings[] = [
                'pattern' => 'happy_path_only_auth',
                'why' => 'This is a security-sensitive test file (path matches auth/token/security pattern) that only tests the happy path — successful authentication, valid tokens, authorized access. It does not test denial paths: invalid credentials, expired tokens, revoked sessions, tampered JWTs, replay attacks, authorization bypasses, or fail-closed behavior. A security boundary without negative tests is not proven secure.',
                'risk' => 'HIGH: Security boundaries without negative tests cannot prove they deny unauthorized access. Authorization bypasses, token forgery, session fixation, replay attacks, and fail-open behavior will not be caught by the test suite. This is a security gap, not just a test quality issue.',
                'suggestion' => 'Add these negative tests for every security boundary: (1) invalid credentials — wrong password, wrong username, empty fields, (2) expired tokens — expired JWT, expired session, expired refresh token, (3) revoked tokens — logout, admin revocation, password change invalidation, (4) tampered tokens — modified JWT payload, invalid signature, (5) replay attacks — reused nonce, reused token, (6) authorization bypass — user accessing another user\'s resource, (7) fail-closed — database error, cache error, dependency failure should result in denial, not silent success. Each test must use expectException or explicit false assertions.',
            ];
        }
    }

    // ------------------------------------------------------------------
    // Pattern 7: Missing failure assertions — tests have assertions but
    //            no failure/exception/edge-case testing
    // ------------------------------------------------------------------
    if ($hasTestMethod && $hasAssertion) {
        $hasFailureTest = (bool) preg_match(
            '/(expectException|expectErrorMessage|assertThrows|willThrowException|' .
            'test.*fail|test.*error|test.*exception|test.*edge|test.*boundary|' .
            'catch\s*\(|@expect|fail.*case|error.*path)/i',
            $content
        );

        if (!$hasFailureTest && $assertCount > 0) {
            $findings[] = [
                'pattern' => 'missing_failure_assertions',
                'why' => 'This test file has assertions but none of them test failure behavior. There are no expectException calls, no error-path tests, no edge-case tests, and no boundary-condition tests. The assertions only verify that things work when input is valid and dependencies are available. This leaves the error-handling code paths completely untested.',
                'risk' => 'When the system encounters errors in production (network failure, database timeout, invalid config, missing dependency), the error-handling code may itself be broken — wrong exception type, leaked sensitive data in error messages, fail-open instead of fail-closed, silent swallowing of errors, or infinite retry loops. None of these will be caught because the error paths have never been executed by tests.',
                'suggestion' => 'Add failure assertions for each error-prone operation: (1) use expectException to verify the correct exception type is thrown for invalid input, (2) test dependency failures — mock dependencies to throw exceptions and verify the system handles them gracefully, (3) test edge cases — empty strings, null values, maximum lengths, boundary numbers, (4) verify error messages do not leak sensitive data (stack traces, SQL queries, file paths, credentials).',
            ];
        }
    }

    // ------------------------------------------------------------------
    // Pattern 8: Duplicated test logic — multiple test methods testing the
    //            exact same behavior with different names
    // ------------------------------------------------------------------
    if ($hasTestMethod) {
        // Extract test method bodies and look for near-duplicate assertion patterns
        preg_match_all(
            '/public\s+function\s+(test_\w+)\s*\([^)]*\)\s*\{([^}]*(?:\{[^}]*\}[^}]*)*)\}/s',
            $content,
            $methodMatches,
            PREG_SET_ORDER
        );

        if (count($methodMatches) >= 2) {
            $methodBodies = [];
            foreach ($methodMatches as $match) {
                $methodName = $match[1];
                $body = $match[2];
                // Normalize: strip whitespace, comments, and variable names to find structural duplicates
                $normalized = preg_replace('/\\$\w+/', '$VAR', $body);
                $normalized = preg_replace('/\s+/', ' ', $normalized);
                $normalized = trim($normalized);
                $methodBodies[] = ['name' => $methodName, 'body' => $normalized];
            }

            $duplicates = [];
            $seen = [];
            foreach ($methodBodies as $method) {
                if (isset($seen[$method['body']])) {
                    $duplicates[] = $seen[$method['body']] . ' and ' . $method['name'];
                } else {
                    $seen[$method['body']] = $method['name'];
                }
            }

            if (!empty($duplicates)) {
                $findings[] = [
                    'pattern' => 'duplicated_test_logic',
                    'why' => 'Multiple test methods in this file test the exact same behavior with different names. Duplicated methods found: ' . implode(', ', $duplicates) . '. This inflates test count without increasing coverage. When the behavior changes, all duplicates must be updated, increasing maintenance burden and the risk that some duplicates are updated incorrectly.',
                    'risk' => 'Two risks: (1) False confidence — test count looks high, but unique behavioral coverage is lower than it appears. (2) Maintenance risk — when behavior changes, duplicated tests may diverge, with some testing the old behavior and some testing the new, creating confusion about what the correct behavior actually is.',
                    'suggestion' => 'Merge duplicated test methods into a single method with a data provider. Use PHPUnit @dataProvider to test multiple input/output combinations with one test method. This makes it obvious what is being tested, reduces duplication, and makes failures easier to diagnose. If the methods seem different but have identical structure, consider whether they should test different aspects of behavior instead.',
                ];
            }
        }
    }

    // ------------------------------------------------------------------
    // Pattern 9: Trivial smoke tests — tests under 15 lines with 1 assertion
    //            (improved detection and messaging)
    // ------------------------------------------------------------------
    if ($hasTestMethod && $assertCount === 1 && $lineCount < 15) {
        $findings[] = [
            'pattern' => 'trivial_smoke_test',
            'why' => 'This test file is under 15 lines and contains only a single assertion. A test this small with only one assertion cannot meaningfully verify complex behavior. It likely tests only the most obvious happy-path case, leaving all edge cases, error paths, boundary conditions, and security denial paths untested. Even for simple classes, a single assertion proves only one narrow scenario.',
            'risk' => 'Creates fake coverage — the file is counted as "tested" by coverage tools, but only one scenario was verified. Any behavior change outside that single scenario will not be caught. For security-sensitive code, a single happy-path assertion proves nothing about the security boundary.',
            'suggestion' => 'Expand this test to cover multiple behavioral paths: (1) happy path with valid input, (2) at least one invalid input that is rejected, (3) boundary condition (empty string, null, max value, min value), (4) error path (missing dependency, timeout, invalid state). Use a data provider if the test structure is repetitive. If the class truly has only one behavior worth testing, consider whether the class is too simple to exist as a separate unit.',
        ];
    }

    // ------------------------------------------------------------------
    // Pattern 10: Tests without any assertions (existing pattern, enhanced)
    // ------------------------------------------------------------------
    if ($hasTestMethod && !$hasAssertion) {
        $findings[] = [
            'pattern' => 'no_assertions',
            'why' => 'Test methods exist but contain no assertions whatsoever. A test method without assertions proves nothing — it only demonstrates that the code executed without throwing an uncaught exception. This is equivalent to having no test at all.',
            'risk' => 'Creates fake coverage. Coverage tools will count the executed lines as "tested," but no behavior was verified. Broken behavior, wrong return values, missing side effects, and security bypasses will not be detected.',
            'suggestion' => 'Add assertions that verify expected behavior. Every test should assert something: return values, state changes, exceptions thrown, side effects performed, or interactions with collaborators. If you cannot think of anything to assert, the test may not be testing meaningful behavior.',
        ];
    }

    // ------------------------------------------------------------------
    // Pattern 11: Fail-closed behavior not tested (existing, enhanced)
    // ------------------------------------------------------------------
    $isSecurityFile = (bool) preg_match('/(Authenticat|Authoriz|TokenVerif|AccessControl)/', basename($path));
    if ($isSecurityFile && $hasTestMethod) {
        $hasFailClosed = (bool) preg_match('/(fail.*closed|fail.*safe|when.*fail|exception|error.*path|fail.*open)/i', $content);
        if (!$hasFailClosed) {
            $findings[] = [
                'pattern' => 'missing_fail_closed_test',
                'why' => 'This security file does not test fail-closed behavior. When the system encounters an error (database failure, timeout, missing config, network error), it must deny access rather than grant it. Without explicit tests for this behavior, the system may fail open — granting access when it should deny it.',
                'risk' => 'HIGH: A security system that fails open is worse than no security system at all — it creates the illusion of protection while leaving the door open during failures. Database outages, cache failures, or network errors could result in unauthorized access.',
                'suggestion' => 'Add tests that simulate dependency failures and verify denial: (1) mock the database to throw an exception and verify access is denied, (2) mock the token store to return null and verify authentication fails, (3) simulate a timeout and verify the system denies rather than allows, (4) verify that error handling does not accidentally grant default access.',
            ];
        }
    }

    // ------------------------------------------------------------------
    // Pattern 12: Getter/setter-only tests (existing, enhanced)
    // ------------------------------------------------------------------
    if (preg_match_all('/->get\w+\(\)/', $content, $getMatches) &&
        preg_match_all('/\$this->assert(Equals|Same|InstanceOf)/', $content, $assertMatches)) {
        $getterCalls = count($getMatches[0]);
        $getterAssertions = 0;
        foreach ($lines as $line) {
            if (preg_match('/->get\w+\(\)/', $line) && preg_match('/assert/', $line)) {
                $getterAssertions++;
            }
        }
        if ($getterAssertions > 0 && $getterCalls === $getterAssertions && $getterCalls >= 1) {
            $findings[] = [
                'pattern' => 'getter_setter_only_test',
                'why' => 'All assertions in this test file verify only getter/setter behavior. Getters and setters are trivial data accessors — testing them proves the language\'s property access works, not that any business logic is correct. Real behavior lives in methods that transform data, make decisions, enforce rules, or interact with external systems.',
                'risk' => 'Coverage inflation — the class appears tested because assertion count is non-zero, but no meaningful behavior was verified. Business logic, validation, authorization, and state transitions remain untested.',
                'suggestion' => 'Add tests for the class\'s actual behavior: methods that enforce business rules, validate input, transform data, make authorization decisions, or interact with external systems. If the class only has getters and setters, consider whether it should be a simple data class (DTO/Value Object) that does not need extensive testing, or whether it is missing behavior that should be implemented and tested.',
            ];
        }
    }

    return $findings;
}

/**
 * Scan a directory for test files
 */
function scanTestFiles(string $dir): void
{
    global $findings, $filesScanned, $shallowCount, $root;

    $phpFiles = glob($dir . '/*.php');
    foreach ($phpFiles ?: [] as $phpFile) {
        $filesScanned++;
        $issues = analyzeTestFile($phpFile);
        if (!empty($issues)) {
            $shallowCount++;
            $relativePath = str_replace($root . '/', '', $phpFile);

            // Determine severity based on security sensitivity and finding type
            $isSecurityFile = (bool) preg_match('/(Authenticat|Authoriz|TokenVerif|AccessControl|Security)/', $phpFile);
            $hasSecurityIssue = false;
            foreach ($issues as $issue) {
                if (preg_match('/(security|fail-closed|fail.*open|negative|NOT proven|auth|token|access|deny|bypass)/i', $issue['why'])) {
                    $hasSecurityIssue = true;
                    break;
                }
            }

            $severity = $hasSecurityIssue ? 'HIGH' : 'MEDIUM';

            foreach ($issues as $issue) {
                $findings[] = [
                    'severity' => $severity,
                    'path' => $relativePath,
                    'pattern' => $issue['pattern'],
                    'why' => $issue['why'],
                    'risk' => $issue['risk'],
                    'suggestion' => $issue['suggestion'],
                ];
            }
        }
    }

    // Recurse
    $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($subdirs ?: [] as $subdir) {
        scanTestFiles($subdir);
    }
}

echo "=== Shallow Test Detection ===\n";
echo "Scanning: $testsDir\n\n";

if (!is_dir($testsDir)) {
    echo "RED: tests/ directory not found\n";
    exit(1);
}

scanTestFiles($testsDir);

// Print findings
$highCount = 0;
$mediumCount = 0;
$lowCount = 0;

foreach ($findings as $f) {
    switch ($f['severity']) {
        case 'HIGH': $highCount++; break;
        case 'MEDIUM': $mediumCount++; break;
        case 'LOW': $lowCount++; break;
    }
}

$toolName = 'shallow-tests';

if ($writeBaselinePath !== null) {
    $target = $writeBaselinePath === '1' ? $baselinePath : $root . '/' . ltrim($writeBaselinePath, '/');
    avax_gate_write_baseline($target, $toolName, $findings, $root);
    echo "BASELINE_WRITTEN {$target}\n";
    echo "Entries: " . count($findings) . "\n";
    exit(0);
}

if ($mode === 'baseline') {
    $result = avax_gate_compare_with_baseline($toolName, $findings, $baselinePath, $root);
    avax_gate_print_baseline_result($toolName, $result);
    exit($result['valid'] ? 0 : 1);
}

if ($mode === 'changed') {
    $changedFiles = avax_gate_changed_files($root);
    $changedFindings = avax_gate_filter_changed_findings($findings, $changedFiles);
    $valid = avax_gate_print_changed_result($toolName, $changedFiles, $changedFindings, true);
    exit($valid ? 0 : 1);
}

if (empty($findings)) {
    echo "GREEN — No shallow tests detected.\n";
    echo "Files scanned: $filesScanned\n";
    exit(0);
}

echo "=== FINDINGS ===\n\n";

$severityOrder = ['HIGH', 'MEDIUM', 'LOW'];
foreach ($severityOrder as $severity) {
    $severityFindings = array_filter($findings, fn($f) => $f['severity'] === $severity);
    if (empty($severityFindings)) {
        continue;
    }

    echo "--- $severity (" . count($severityFindings) . ") ---\n";
    foreach ($severityFindings as $f) {
        echo "  [$severity] {$f['path']}\n";
        echo "    Pattern: {$f['pattern']}\n";
        echo "    WHY: {$f['why']}\n";
        echo "    RISK: {$f['risk']}\n";
        echo "    SUGGESTION: {$f['suggestion']}\n\n";
    }
}

echo "=== SUMMARY ===\n";
echo "Files scanned: $filesScanned\n";
echo "Shallow files: $shallowCount\n";
echo "Total findings: " . count($findings) . "\n";
echo "HIGH: $highCount (security-sensitive shallow tests)\n";
echo "MEDIUM: $mediumCount (general shallow tests)\n";
echo "LOW: $lowCount\n";

// Pattern breakdown
$patternCounts = [];
foreach ($findings as $f) {
    $pattern = $f['pattern'];
    $patternCounts[$pattern] = ($patternCounts[$pattern] ?? 0) + 1;
}

if (!empty($patternCounts)) {
    echo "\n=== PATTERN BREAKDOWN ===\n";
    arsort($patternCounts);
    foreach ($patternCounts as $pattern => $count) {
        echo "  $pattern: $count\n";
    }
}

exit(count($findings) > 0 ? 1 : 0);
