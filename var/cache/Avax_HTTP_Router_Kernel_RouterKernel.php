<?php

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;

return ServicePrototype::__set_state(array: [
    'class'              => 'Avax\\HTTP\\Router\\Kernel\\RouterKernel',
    'constructor'        => MethodPrototype::__set_state(array: [
        'name'       => '__construct',
        'parameters' => [
            0 => ParameterPrototype::__set_state(array: [
                'name'       => 'httpRequestRouter',
                'type'       => 'Avax\\HTTP\\Router\\Routing\\HttpRequestRouter',
                'hasDefault' => false,
                'default'    => null,
                'isVariadic' => false,
                'allowsNull' => false,
                'required'   => true,
            ]),
            1 => ParameterPrototype::__set_state(array: [
                'name'       => 'pipelineFactory',
                'type'       => 'Avax\\HTTP\\Router\\Routing\\RoutePipelineFactory',
                'hasDefault' => false,
                'default'    => null,
                'isVariadic' => false,
                'allowsNull' => false,
                'required'   => true,
            ]),
            2 => ParameterPrototype::__set_state(array: [
                'name'       => 'headRequestFallback',
                'type'       => 'Avax\\HTTP\\Router\\Support\\HeadRequestFallback',
                'hasDefault' => false,
                'default'    => null,
                'isVariadic' => false,
                'allowsNull' => false,
                'required'   => true,
            ]),
        ],
    ]),
    'injectedProperties' => [
    ],
    'injectedMethods'    => [
    ],
    'isInstantiable'     => true,
]);
