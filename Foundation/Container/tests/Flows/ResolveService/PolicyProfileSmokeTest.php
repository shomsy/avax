<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class StrictSharedFlowService {}

final class ConditionalInternalService {}

$relaxed = makeTestContainer(config: CreateContainerConfig::create(
    policyProfile: CreateContainerConfig::POLICY_PROFILE_RELAXED
));
$relaxed->bind(abstract: StrictSharedFlowService::class, concrete: StrictSharedFlowService::class)
    ->asFlow(ownerSlice: 'flow.relaxed')
    ->asShared();
$relaxed->bind(abstract: ConditionalInternalService::class, concrete: ConditionalInternalService::class)
    ->asCapability(ownerSlice: 'capability.relaxed')
    ->asInternal()
    ->profiles(profiles: 'prod');

$strict = makeTestContainer(config: CreateContainerConfig::create(
    settings      : ['app_env' => 'prod'],
    policyProfile : CreateContainerConfig::POLICY_PROFILE_RELAXED,
    policyProfiles: ['prod' => CreateContainerConfig::POLICY_PROFILE_STRICT],
    policyFailMode: CreateContainerConfig::POLICY_FAIL_MODE_CLOSED
));
$strict->bind(abstract: StrictSharedFlowService::class, concrete: StrictSharedFlowService::class)
    ->asFlow(ownerSlice: 'flow.strict')
    ->asShared();
$strict->bind(abstract: ConditionalInternalService::class, concrete: ConditionalInternalService::class)
    ->asCapability(ownerSlice: 'capability.strict')
    ->asInternal()
    ->profiles(profiles: 'prod');

$relaxedFindings  = $relaxed->debugGraph()['policyFindings'];
$strictFindings   = $strict->debugGraph()['policyFindings'];
$strictGovernance = $strict->debugGovernance();

$relaxedFlowSeverities        = array_column($relaxedFindings[StrictSharedFlowService::class] ?? [], 'severity', 'code');
$strictFlowSeverities         = array_column($strictFindings[StrictSharedFlowService::class] ?? [], 'severity', 'code');
$relaxedConditionalSeverities = array_column($relaxedFindings[ConditionalInternalService::class] ?? [], 'severity', 'code');
$strictConditionalSeverities  = array_column($strictFindings[ConditionalInternalService::class] ?? [], 'severity', 'code');

assertSame(expected: 'warn', actual: $relaxedFlowSeverities['POL-006'] ?? null, message: 'Relaxed policy profile should keep POL-006 as a warning.');
assertSame(expected: 'error', actual: $strictFlowSeverities['POL-006'] ?? null, message: 'Strict policy profile should escalate POL-006 to an error.');
assertSame(expected: 'warn', actual: $relaxedConditionalSeverities['POL-013'] ?? null, message: 'Relaxed policy profile should keep hidden conditional findings as warnings.');
assertSame(expected: 'error', actual: $strictConditionalSeverities['POL-013'] ?? null, message: 'Strict policy profile should escalate hidden conditional findings to errors.');
assertSame(expected: 'strict', actual: $strictGovernance['profile'] ?? null, message: 'Environment-specific policy profile overrides should be reflected in governance diagnostics.');
assertSame(expected: 'closed', actual: $strictGovernance['failMode'] ?? null, message: 'Governance diagnostics should expose the fail-open/fail-closed posture.');
assertTrue(condition: ($strictGovernance['blocked'] ?? false) === true, message: 'Fail-closed governance should report when policy errors block the composition.');

echo basename(__FILE__) . " ok\n";
