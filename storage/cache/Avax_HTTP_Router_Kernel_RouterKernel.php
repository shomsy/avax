<?php

return \Avax\Container\Features\Think\Model\ServicePrototype::__set_state(array(
   'class' => 'Avax\\HTTP\\Router\\Kernel\\RouterKernel',
   'constructor' => 
  \Avax\Container\Features\Think\Model\MethodPrototype::__set_state(array(
     'name' => '__construct',
     'parameters' => 
    array (
      0 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'httpRequestRouter',
         'type' => 'Avax\\HTTP\\Router\\Routing\\HttpRequestRouter',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      1 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'pipelineFactory',
         'type' => 'Avax\\HTTP\\Router\\Routing\\RoutePipelineFactory',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      2 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'headRequestFallback',
         'type' => 'Avax\\HTTP\\Router\\Support\\HeadRequestFallback',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      3 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'routeExecutor',
         'type' => 'Avax\\HTTP\\Router\\Routing\\RouteExecutor',
         'hasDefault' => false,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => false,
         'required' => true,
      )),
      4 => 
      \Avax\Container\Features\Think\Model\ParameterPrototype::__set_state(array(
         'name' => 'trace',
         'type' => 'Avax\\HTTP\\Router\\Tracing\\RouterTrace',
         'hasDefault' => true,
         'default' => NULL,
         'isVariadic' => false,
         'allowsNull' => true,
         'required' => false,
      )),
    ),
  )),
   'injectedProperties' => 
  array (
  ),
   'injectedMethods' => 
  array (
  ),
   'isInstantiable' => true,
));
