<?php

declare(strict_types=1);

use Avax\Components\Application\Config\System\PublicSurface\Config;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Components\Application\Container\System\Container;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ConnectionPool;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Column\DSL\ColumnDefinition;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Column\Render\ColumnSQLRenderer;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Table\Blueprint;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Column;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Entity;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\GeneratedValue;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Id;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Table;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Repositories\EntityRepository;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Exceptions\QueryException;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\MySQLGrammar;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Exceptions\TransactionException;
use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Capabilities\ValueConversion\ValueCasterInterface;
use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Capabilities\ValueConversion\ValueConversionContext;
use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Foundation\AbstractDTO;
use Avax\Components\HTTP\System\Capabilities\Kernel\AppKernel;
use Avax\Components\HTTP\Context\System\PublicSurface\HttpContext;
use Avax\Components\HTTP\System\Capabilities\Kernel\HttpKernel;
use Avax\Components\HTTP\System\Flows\Routing\ResolveRouteFromHttpRequest;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;
use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\Presentation\View\System\PublicSurface\View;

$classAliases = [
    'Avax\\DataHandling\\DataTransfer\\DataTransfer'                                        => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\DataTransfer\\DataTransfer',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\CastWith'                  => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\DataTransfer\\Capabilities\\Attributes\\CastWith',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\Hidden'                    => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\DataTransfer\\Capabilities\\Attributes\\Hidden',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\ListOf'                    => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\DataTransfer\\Capabilities\\Attributes\\ListOf',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\MapFrom'                   => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\DataTransfer\\Capabilities\\Attributes\\MapFrom',
    ValueCasterInterface::class                                   => ValueCasterInterface::class,
    ValueConversionContext::class                                 => ValueConversionContext::class,
    'Avax\\DataHandling\\DataTransfer\\InspectDataShape\\DataField'                         => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\DataShape\\DataField',
    AbstractDTO::class                                            => AbstractDTO::class,
    'Avax\\DataFoundation\\ObjectHandling\\DTO\\AbstractDTO'      => AbstractDTO::class,
    'Avax\\DataFoundation\\Collection'                                                      => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Collection',
    'Avax\\DataFoundation\\Arrhae'                                                          => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Arrhae',
    'Avax\\DataFoundation\\Collections\\Map\\Map'                                           => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Map\\Map',
    'Avax\\DataFoundation\\Collections\\Set\\Set'                                           => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Set\\Set',
    'Avax\\DataFoundation\\Collections\\DataList\\DataList'                                 => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\DataList\\DataList',
    'Avax\\DataFoundation\\Collections\\Aggregate\\AverageValues'                           => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Operators\\Aggregate\\AverageValues',
    'Avax\\DataFoundation\\Collections\\Aggregate\\SumValues'                               => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Operators\\Aggregate\\SumValues',
    'Avax\\DataFoundation\\Collections\\Transform\\MapValues'                               => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Operators\\Transform\\MapValues',
    'Avax\\DataFoundation\\Collections\\Transform\\FilterValues'                            => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Operators\\Transform\\FilterValues',
    'Avax\\DataFoundation\\Collections\\Search\\MatchTextFuzzily'                           => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\MatchTextFuzzily',
    'Avax\\DataFoundation\\Collections\\Search\\MatchTextPartially'                         => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\MatchTextPartially',
    'Avax\\DataFoundation\\Collections\\Search\\ContainsValue'                              => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\ContainsValue',
    'Avax\\DataFoundation\\Collections\\Search\\SearchValue'                                => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\SearchValue',
    'Avax\\DataFoundation\\Values\\Option\\Option'                                          => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Option\\Option',
    'Avax\\DataFoundation\\Values\\Option\\Some'                                            => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Option\\Some',
    'Avax\\DataFoundation\\Values\\Option\\None'                                            => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Option\\None',
    'Avax\\DataFoundation\\Values\\Result\\Result'                                          => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Result\\Result',
    'Avax\\DataFoundation\\Values\\Result\\Ok'                                              => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Result\\Success',
    'Avax\\DataFoundation\\Values\\Result\\Error'                                           => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Result\\Failure',
    'Avax\\DataFoundation\\Composites\\MapEntry\\MapEntry'                                  => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\MapEntry',
    'Avax\\DataFoundation\\Composites\\Pair\\Pair'                                          => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Pair',
    'Avax\\DataFoundation\\Composites\\Tuple\\Tuple2'                                       => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Tuple2',
    'Avax\\DataFoundation\\Composites\\Tuple\\Tuple3'                                       => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Tuple3',
    'Avax\\DataFoundation\\Composites\\Tuple\\Tuple4'                                       => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Tuple4',
    'Avax\\DataFoundation\\Composites\\Record\\Record'                                      => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Record',
    'Avax\\DataFoundation\\Composites\\Record\\RecordField'                                 => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\RecordField',

    // DataShape ecosystem
    'Avax\\DataFoundation\\DataTransfer\\InspectDataShape\\InspectDataShape' => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\DataShape\\InspectDataShape',
    'Avax\\DataFoundation\\DataTransfer\\InspectDataShape\\DataField'                       => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\DataShape\\DataField',
    'Avax\\DataFoundation\\DataTransfer\\InspectDataShape\\DataFieldType'                   => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\DataShape\\DataFieldType',

    // ObjectReading ecosystem
    'Avax\\DataFoundation\\DataTransfer\\ObjectReading\\ReadDataObject'                     => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\ObjectReading\\ReadDataObject',
    'Avax\\DataFoundation\\DataTransfer\\ObjectReading\\NormalizeDataObjectValue' => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\ObjectReading\\NormalizeDataObjectValue',

    // Serialization flow ecosystem
    'Avax\\DataFoundation\\DataTransfer\\SerializeDataObject\\SerializeDataObject'          => 'Avax\\Components\\DataStack\\Data\\System\\Flows\\SerializeDataObject\\SerializeDataObject',
    'Avax\\DataFoundation\\DataTransfer\\SerializeDataObject\\ConvertDataObjectToArray'     => 'Avax\\Components\\DataStack\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToArray',
    'Avax\\DataFoundation\\DataTransfer\\SerializeDataObject\\ConvertDataObjectToJson'      => 'Avax\\Components\\DataStack\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToJson',
    'Avax\\DataFoundation\\DataTransfer\\SerializeDataObject\\ConvertDataObjectToStdClass' => 'Avax\\Components\\DataStack\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToStdClass',
    'Avax\\DataFoundation\\DataTransfer\\SerializeDataObject\\ConvertDataObjectToJsonApi'   => 'Avax\\Components\\DataStack\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToJsonApi',

    'Avax\\Text\\Text'                                                => 'Avax\\Components\\Application\\Text\\System\\PublicSurface\\Text',
    'Avax\\DataHandling\\ObjectHandling\\DTO\\DTOValidationException' => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\ObjectHandling\\DTO\\DTOValidationException',
    'Avax\\DataHandling\\Validation\\Attributes\\Rules\\EmailRule'    => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Validation\\Attributes\\Rules\\EmailRule',
    'Avax\\DataHandling\\Validation\\Attributes\\Rules\\MinLengthRule' => 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Validation\\Attributes\\Rules\\MinLengthRule',

    // Router compat aliases - only core interfaces exist at new location
    // Other Router aliases removed: old component structure was replaced by HTTP/Router suite
    Router::class                                                 => Router::class,
    RouterInterface::class                                        => RouterInterface::class,
    RouterRuntimeInterface::class                                 => RouterRuntimeInterface::class,

    'Avax\\Database\\Database'                                                                  => 'Avax\\Components\\DataStack\\Database\\System\\PublicSurface\\Database',
    'Avax\\Database\\EntityManager'                                                             => 'Avax\\Components\\DataStack\\Database\\System\\PublicSurface\\EntityManager',
    'Avax\\Database\\Migrations'                                                                => 'Avax\\Components\\DataStack\\Database\\System\\PublicSurface\\Migrations',
    'Avax\\Database\\Query'                                                                     => 'Avax\\Components\\DataStack\\Database\\System\\PublicSurface\\Query',
    'Avax\\Database\\Schema'                                                                    => 'Avax\\Components\\DataStack\\Database\\System\\PublicSurface\\Schema',
    'Avax\\Database\\Telemetry'                                                                 => 'Avax\\Components\\DataStack\\Database\\System\\PublicSurface\\Telemetry',
    'Avax\\Database\\Transactions'                                                              => 'Avax\\Components\\DataStack\\Database\\System\\PublicSurface\\Transactions',
    Connections::class                                            => Connections::class,
    ConnectionPool::class                                         => ConnectionPool::class,
    ColumnDefinition::class                                       => ColumnDefinition::class,
    ColumnSQLRenderer::class                                      => ColumnSQLRenderer::class,
    Blueprint::class                                              => Blueprint::class,
    Column::class                                                 => Column::class,
    Entity::class                                                 => Entity::class,
    GeneratedValue::class                                         => GeneratedValue::class,
    Id::class                                                     => Id::class,
    Table::class                                                  => Table::class,
    EntityRepository::class                                       => EntityRepository::class,
    QueryBuilder::class                                           => QueryBuilder::class,
    QueryException::class                                         => QueryException::class,
    MySQLGrammar::class                                           => MySQLGrammar::class,
    TransactionException::class                                   => TransactionException::class,

    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\ServerRequest'                        => 'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\ServerRequest',
    'Avax\\HTTP\\Request\\Request'                                                              => 'Avax\\Components\\HTTP\\Request\\System\\PublicSurface\\RequestInterface',
    'Avax\\Logging\\LoggerFactory'                                                              => 'Avax\\Components\\Logging\\System\\Configuration\\RegisterLogging',
    'Avax\\Cache\\Cache'                                                                        => 'Avax\\Components\\Application\\Cache\\System\\PublicSurface\\Cache',
    'Avax\\DumpDebugger\\DumpDebugger'                                                          => 'Avax\\Components\\DumpDebugger\\System\\PublicSurface\\Dump',
    'Avax\\Components\\Application\\Container\\System\\PublicSurface\\Container'                => 'Avax\\Components\\Application\\Container\\System\\Container',
    HttpKernel::class                                             => HttpKernel::class,
    AppKernel::class                                              => AppKernel::class,
    ResolveRouteFromHttpRequest::class                            => ResolveRouteFromHttpRequest::class,
    'Avax\\HTTP\\Response\\Response'                                                            => 'Avax\\Components\\HTTP\\Response\\System\\PublicSurface\\Response',
    'Avax\\HTTP\\Response\\Capabilities\\Headers\\ResponseHeaders'                              => 'Avax\\Components\\HTTP\\Response\\System\\Capabilities\\Headers\\ResponseHeaders',
    'Avax\\HTTP\\Response\\Flows\\BuildResponse\\BuildResponse'                                 => 'Avax\\Components\\HTTP\\Response\\System\\Flows\\BuildResponse\\BuildResponse',
    'Avax\\HTTP\\RouterBootstrapper'                                                            => 'Avax\\Components\\HTTP\\System\\Configuration\\RouterBootstrapper',
    Session::class                                                => Session::class,
    HttpContext::class                                            => HttpContext::class,
    View::class                                                   => View::class,
    Filesystem::class                                             => Filesystem::class,
    Config::class                                                 => Config::class,
    'Avax\\Container\\ContainerInterface' => ContainerInterface::class,
];

foreach ($classAliases as $alias => $target) {
    if ($alias === $target) {
        continue;
    }

    if (class_exists($alias, false)) {
        continue;
    }

    if (interface_exists($alias, false)) {
        continue;
    }

    if (interface_exists($target) || class_exists($target)) {
        class_alias(
            class     : $target,
            alias     : $alias,
            autoload  : false,
        );
    }
}
