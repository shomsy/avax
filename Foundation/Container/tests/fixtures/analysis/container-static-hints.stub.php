<?php

declare(strict_types=1);

/**
 * Generated static analysis hints from canonical authored truth.
 *
 * @phpstan-type ContainerServiceId
 *               'HintConditionalService'|'HintIdentityService'|'HintPipelineStepA'|'HintPipelineStepB'|'HintRuntimeInputConsumer'
 * @phpstan-type ContainerGroup 'hint.pipeline'
 * @phpstan-type ContainerSlice 'capability.identity'|'flow.hints'
 * @psalm-type ContainerServiceId =
 *             'HintConditionalService'|'HintIdentityService'|'HintPipelineStepA'|'HintPipelineStepB'|'HintRuntimeInputConsumer'
 * @psalm-type ContainerGroup = 'hint.pipeline'
 * @psalm-type ContainerSlice = 'capability.identity'|'flow.hints'
 */
return [
    'schemaVersion' => 1,
    'serviceIds'    =>
        [
            0 => 'HintConditionalService',
            1 => 'HintIdentityService',
            2 => 'HintPipelineStepA',
            3 => 'HintPipelineStepB',
            4 => 'HintRuntimeInputConsumer',
        ],
    'sliceExports'  =>
        [
            'capability.identity' =>
                [
                    0 => 'HintConditionalService',
                    1 => 'HintIdentityService',
                ],
            'flow.hints'          =>
                [
                ],
        ],
    'sliceImports'  =>
        [
            'capability.identity' =>
                [
                ],
            'flow.hints'          =>
                [
                    0 => 'capability.identity',
                ],
        ],
    'groups'        =>
        [
            'hint.pipeline' =>
                [
                    0 => 'HintPipelineStepA',
                    1 => 'HintPipelineStepB',
                ],
        ],
    'runtimeInputs' =>
        [
            'HintConditionalService'   =>
                [
                ],
            'HintIdentityService'      =>
                [
                ],
            'HintPipelineStepA'        =>
                [
                ],
            'HintPipelineStepB'        =>
                [
                ],
            'HintRuntimeInputConsumer' =>
                [
                    0 => 'token',
                ],
        ],
    'conditionals'  =>
        [
            'HintConditionalService' =>
                [
                    'profiles' =>
                        [
                            0 => 'prod',
                        ],
                    'flags'    =>
                        [
                            0 => 'beta',
                        ],
                    'tenants'  =>
                        [
                        ],
                    'regions'  =>
                        [
                        ],
                    'modes'    =>
                        [
                        ],
                    'fallback' => false,
                ],
        ],
];
