<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class StrictSharedFlowService
{
}

final class ConditionalInternalService
{
}

$relaxed = makeTestContainer(CreateContainerConfig::create(
    policyProfile: CreateContainerConfig::POLICY_PROFILE_RELAXED
));
$relaxed->bind(StrictSharedFlowService::class, StrictSharedFlowService::class)
    ->asFlow('flow.relaxed')
    ->asShared();
$relaxed->bind(ConditionalInternalService::class, ConditionalInternalService::class)
    ->asCapability('capability.relaxed')
    ->asInternal()
    ->profiles('prod');

$strict = makeTestContainer(CreateContainerConfig::create(
    settings: ['app_env' => 'prod'],
    policyProfile: CreateContainerConfig::POLICY_PROFILE_RELAXED,
    policyProfiles: ['prod' => CreateContainerConfig::POLICY_PROFILE_STRICT],
    policyFailMode: CreateContainerConfig::POLICY_FAIL_MODE_CLOSED
));
$strict->bind(StrictSharedFlowService::class, StrictSharedFlowService::class)
    ->asFlow('flow.strict')
    ->asShared();
$strict->bind(ConditionalInternalService::class, ConditionalInternalService::class)
    ->asCapability('capability.strict')
    ->asInternal()
    ->profiles('prod');

$relaxedFindings = $relaxed->debugGraph()['policyFindings'];
$strictFindings = $strict->debugGraph()['policyFindings'];
$strictGovernance = $strict->debugGovernance();

$relaxedFlowSeverities = array_column($relaxedFindings[StrictSharedFlowService::class] ?? [], 'severity', 'code');
$strictFlowSeverities = array_column($strictFindings[StrictSharedFlowService::class] ?? [], 'severity', 'code');
$relaxedConditionalSeverities = array_column($relaxedFindings[ConditionalInternalService::class] ?? [], 'severity', 'code');
$strictConditionalSeverities = array_column($strictFindings[ConditionalInternalService::class] ?? [], 'severity', 'code');

assertSame('warn', $relaxedFlowSeverities['POL-006'] ?? null, 'Relaxed policy profile should keep POL-006 as a warning.');
assertSame('error', $strictFlowSeverities['POL-006'] ?? null, 'Strict policy profile should escalate POL-006 to an error.');
assertSame('warn', $relaxedConditionalSeverities['POL-013'] ?? null, 'Relaxed policy profile should keep hidden conditional findings as warnings.');
assertSame('error', $strictConditionalSeverities['POL-013'] ?? null, 'Strict policy profile should escalate hidden conditional findings to errors.');
assertSame('strict', $strictGovernance['profile'] ?? null, 'Environment-specific policy profile overrides should be reflected in governance diagnostics.');
assertSame('closed', $strictGovernance['failMode'] ?? null, 'Governance diagnostics should expose the fail-open/fail-closed posture.');
assertTrue(($strictGovernance['blocked'] ?? false) === true, 'Fail-closed governance should report when policy errors block the composition.');

echo basename(__FILE__) . " ok\n";
