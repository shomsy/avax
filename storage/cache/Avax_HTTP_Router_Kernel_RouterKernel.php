<?php

declare(strict_types=1);

return Avax\Container\Features\Think\Model\ServicePrototype::__set_state([
                                                                             'class'              => 'Avax\\HTTP\\Router\\Kernel\\RouterKernel',
                                                                             'constructor'        => Avax\Container\Features\Think\Model\MethodPrototype::__set_state([
                                                                                                                                                                          'name'       => '__construct',
                                                                                                                                                                          'parameters' => [
                                                                                                                                                                              0 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                                                                                                                                                                                                                                                           'name'     => 'httpRequestRouter',
                                                                                                                                                                                                                                                           'type'     => 'Avax\\HTTP\\Router\\Routing\\HttpRequestRouter',
         'hasDefault' => false,
                                                                                                                                                                                                                                                           'default'  => null,
         'isVariadic' => false,
         'allowsNull' => false,
                                                                                                                                                                                                                                                           'required' => true,
                                                                                                                                                                                                                                                       ]),
                                                                                                                                                                              1 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                                                                                                                                                                                                                                                           'name'     => 'pipelineFactory',
                                                                                                                                                                                                                                                           'type'     => 'Avax\\HTTP\\Router\\Routing\\RoutePipelineFactory',
         'hasDefault' => false,
                                                                                                                                                                                                                                                           'default'  => null,
         'isVariadic' => false,
         'allowsNull' => false,
                                                                                                                                                                                                                                                           'required' => true,
                                                                                                                                                                                                                                                       ]),
                                                                                                                                                                              2 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                                                                                                                                                                                                                                                           'name'     => 'headRequestFallback',
                                                                                                                                                                                                                                                           'type'     => 'Avax\\HTTP\\Router\\Support\\HeadRequestFallback',
         'hasDefault' => false,
                                                                                                                                                                                                                                                           'default'  => null,
         'isVariadic' => false,
         'allowsNull' => false,
                                                                                                                                                                                                                                                           'required' => true,
                                                                                                                                                                                                                                                       ]),
                                                                                                                                                                              3 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                                                                                                                                                                                                                                                           'name'     => 'routeExecutor',
                                                                                                                                                                                                                                                           'type'     => 'Avax\\HTTP\\Router\\Routing\\RouteExecutor',
         'hasDefault' => false,
                                                                                                                                                                                                                                                           'default'  => null,
         'isVariadic' => false,
         'allowsNull' => false,
                                                                                                                                                                                                                                                           'required' => true,
                                                                                                                                                                                                                                                       ]),
                                                                                                                                                                              4 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                                                                                                                                                                                                                                                           'name'     => 'trace',
                                                                                                                                                                                                                                                           'type'     => 'Avax\\HTTP\\Router\\Tracing\\RouterTrace',
         'hasDefault' => true,
                                                                                                                                                                                                                                                           'default'  => null,
         'isVariadic' => false,
         'allowsNull' => true,
                                                                                                                                                                                                                                                           'required' => false,
                                                                                                                                                                                                                                                       ]),
                                                                                                                                                                          ],
                                                                                                                                                                      ]),
                                                                             'injectedProperties' => [
                                                                             ],
                                                                             'injectedMethods'    => [
                                                                             ],
   'isInstantiable' => true,
                                                                         ]);
