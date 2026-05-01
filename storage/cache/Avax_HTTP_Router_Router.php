<?php

declare(strict_types=1);

return Avax\Container\Features\Think\Model\ServicePrototype::__set_state([
                                                                             'class'              => 'Avax\\HTTP\\Router\\Router',
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
                                                                                                                                                                                                                                                           'name'     => 'kernel',
                                                                                                                                                                                                                                                           'type'     => 'Avax\\HTTP\\Router\\Kernel\\RouterKernel',
         'hasDefault' => false,
                                                                                                                                                                                                                                                           'default'  => null,
         'isVariadic' => false,
         'allowsNull' => false,
                                                                                                                                                                                                                                                           'required' => true,
                                                                                                                                                                                                                                                       ]),
                                                                                                                                                                              2 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                                                                                                                                                                                                                                                           'name'     => 'fallbackManager',
                                                                                                                                                                                                                                                           'type'     => 'Avax\\HTTP\\Router\\Support\\FallbackManager',
         'hasDefault' => false,
                                                                                                                                                                                                                                                           'default'  => null,
         'isVariadic' => false,
         'allowsNull' => false,
                                                                                                                                                                                                                                                           'required' => true,
                                                                                                                                                                                                                                                       ]),
                                                                                                                                                                              3 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                                                                                                                                                                                                                                                           'name'     => 'errorFactory',
                                                                                                                                                                                                                                                           'type'     => 'Avax\\HTTP\\Router\\Routing\\ErrorResponseFactory',
         'hasDefault' => false,
                                                                                                                                                                                                                                                           'default'  => null,
         'isVariadic' => false,
         'allowsNull' => false,
                                                                                                                                                                                                                                                           'required' => true,
                                                                                                                                                                                                                                                       ]),
                                                                                                                                                                              4 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                                                                                                                                                                                                                                                           'name'     => 'dslRouter',
                                                                                                                                                                                                                                                           'type'     => 'Avax\\HTTP\\Router\\RouterInterface',
         'hasDefault' => true,
                                                                                                                                                                                                                                                           'default'  => null,
         'isVariadic' => false,
         'allowsNull' => true,
                                                                                                                                                                                                                                                           'required' => false,
                                                                                                                                                                                                                                                       ]),
                                                                                                                                                                              5 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                                                                                                                                                                                                                                                           'name'     => 'groupStack',
                                                                                                                                                                                                                                                           'type'     => 'Avax\\HTTP\\Router\\Routing\\RouteGroupStack',
         'hasDefault' => true,
                                                                                                                                                                                                                                                           'default'  => null,
         'isVariadic' => false,
         'allowsNull' => true,
                                                                                                                                                                                                                                                           'required' => false,
                                                                                                                                                                                                                                                       ]),
                                                                                                                                                                              6 => Avax\Container\Features\Think\Model\ParameterPrototype::__set_state([
                                                                                                                                                                                                                                                           'name'     => 'routeRegistry',
                                                                                                                                                                                                                                                           'type'     => 'Avax\\HTTP\\Router\\Support\\RouteRegistry',
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
