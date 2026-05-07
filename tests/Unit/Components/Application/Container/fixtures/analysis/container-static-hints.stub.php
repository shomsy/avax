<?php

declare(strict_types=1);

/**
 * Generated static analysis hints from canonical authored truth.
 *
 * @phpstan-type ContainerServiceId
 *               'HintConditionalService'|'HintIdentityService'|'HintPipelineStepA'|'HintPipelineStepB'|'HintRuntimeInputConsumer'
 * @phpstan-type ContainerGroup 'hint.pipeline'
 * @phpstan-type ContainerSlice 'capability.identity'|'flow.hints'
 *
 * @psalm-type ContainerServiceId =
 *             'HintConditionalService'|'HintIdentityService'|'HintPipelineStepA'|'HintPipelineStepB'|'HintRuntimeInputConsumer'
 * @psalm-type ContainerGroup = 'hint.pipeline'
 * @psalm-type ContainerSlice = 'capability.identity'|'flow.hints'
 */
return [
    'schemaVersion' => 1,
    'serviceIds'    => [
        'HintConditionalService',
        'HintIdentityService',
        'HintPipelineStepA',
        'HintPipelineStepB',
        'HintRuntimeInputConsumer',
    ],
    'sliceExports'  => [
        'capability.identity' => [
            'HintConditionalService',
            'HintIdentityService',
        ],
        'flow.hints'          => [
        ],
    ],
    'sliceImports'  => [
        'capability.identity' => [
        ],
        'flow.hints'          => [
            'capability.identity',
        ],
    ],
    'groups'        => [
        'hint.pipeline' => [
            'HintPipelineStepA',
            'HintPipelineStepB',
        ],
    ],
    'runtimeInputs' => [
        'HintConditionalService'   => [
        ],
        'HintIdentityService'      => [
        ],
        'HintPipelineStepA'        => [
        ],
        'HintPipelineStepB'        => [
        ],
        'HintRuntimeInputConsumer' => [
            'token',
        ],
    ],
    'conditionals'  => [
        'HintConditionalService' => [
            'profiles' => [
                'prod',
            ],
            'flags'    => [
                'beta',
            ],
            'tenants'  => [
            ],
            'regions'  => [
            ],
            'modes'    => [
            ],
            'fallback' => false,
        ],
    ],
];
