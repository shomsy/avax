<?php

declare(strict_types=1);

/**
 * Generated static analysis hints from canonical authored truth.
 *
 * @phpstan-type ContainerServiceId 'HintConditionalService'|'HintIdentityService'|'HintPipelineStepA'|'HintPipelineStepB'|'HintRuntimeInputConsumer'
 * @phpstan-type ContainerGroup 'hint.pipeline'
 * @phpstan-type ContainerSlice 'capability.identity'|'flow.hints'
 * @psalm-type ContainerServiceId = 'HintConditionalService'|'HintIdentityService'|'HintPipelineStepA'|'HintPipelineStepB'|'HintRuntimeInputConsumer'
 * @psalm-type ContainerGroup = 'hint.pipeline'
 * @psalm-type ContainerSlice = 'capability.identity'|'flow.hints'
 */
return array (
  'schemaVersion' => 1,
  'serviceIds' => 
  array (
    0 => 'HintConditionalService',
    1 => 'HintIdentityService',
    2 => 'HintPipelineStepA',
    3 => 'HintPipelineStepB',
    4 => 'HintRuntimeInputConsumer',
  ),
  'sliceExports' => 
  array (
    'capability.identity' => 
    array (
      0 => 'HintConditionalService',
      1 => 'HintIdentityService',
    ),
    'flow.hints' => 
    array (
    ),
  ),
  'sliceImports' => 
  array (
    'capability.identity' => 
    array (
    ),
    'flow.hints' => 
    array (
      0 => 'capability.identity',
    ),
  ),
  'groups' => 
  array (
    'hint.pipeline' => 
    array (
      0 => 'HintPipelineStepA',
      1 => 'HintPipelineStepB',
    ),
  ),
  'runtimeInputs' => 
  array (
    'HintConditionalService' => 
    array (
    ),
    'HintIdentityService' => 
    array (
    ),
    'HintPipelineStepA' => 
    array (
    ),
    'HintPipelineStepB' => 
    array (
    ),
    'HintRuntimeInputConsumer' => 
    array (
      0 => 'token',
    ),
  ),
  'conditionals' => 
  array (
    'HintConditionalService' => 
    array (
      'profiles' => 
      array (
        0 => 'prod',
      ),
      'flags' => 
      array (
        0 => 'beta',
      ),
      'tenants' => 
      array (
      ),
      'regions' => 
      array (
      ),
      'modes' => 
      array (
      ),
      'fallback' => false,
    ),
  ),
);
