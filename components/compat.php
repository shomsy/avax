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
use Avax\Components\DataStack\Data\System\Capabilities\ObjectHandling\DTO\AbstractDTO;
use Avax\Framework\System\PublicSurface\AppKernel;
use Avax\Components\HTTP\Context\System\PublicSurface\HttpContext;
use Avax\Framework\System\PublicSurface\HttpKernel;
use Avax\HTTP\ResolveRouteFromHttpRequest;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;
use Avax\Components\Identity\Sessions\System\PublicSurface\Session;
use Avax\Components\Presentation\Views\System\PublicSurface\View;

$classAliases = [
    'Avax\\DataHandling\\DataTransfer\\DataTransfer'                                        => 'Avax\\DataFoundation\\DataTransfer\\DataTransfer',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\CastWith'                  => 'Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\CastWith',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\Hidden'                    => 'Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\Hidden',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\ListOf'                    => 'Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\ListOf',
    'Avax\\DataHandling\\DataTransfer\\Capabilities\\Attributes\\MapFrom'                   => 'Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\MapFrom',
    ValueCasterInterface::class                                   => \Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Capabilities\ValueConversion\ValueCasterInterface::class,
    ValueConversionContext::class                                 => \Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Capabilities\ValueConversion\ValueConversionContext::class,
    'Avax\\DataHandling\\DataTransfer\\InspectDataShape\\DataField'                         => 'Avax\\Components\\Data\\System\\Capabilities\\DataShape\\DataField',
    AbstractDTO::class                                            => \Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Foundation\AbstractDTO::class,
    \Avax\DataFoundation\ObjectHandling\DTO\AbstractDTO::class    => \Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Foundation\AbstractDTO::class,
    'Avax\\DataFoundation\\Collection'                                                      => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Collection',
    'Avax\\DataFoundation\\Arrhae'                                                          => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Arrhae',
    'Avax\\DataFoundation\\Collections\\Map\\Map'                                           => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Map\\Map',
    'Avax\\DataFoundation\\Collections\\Set\\Set'                                           => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Set\\Set',
    'Avax\\DataFoundation\\Collections\\DataList\\DataList'                                 => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\DataList\\DataList',
    'Avax\\DataFoundation\\Collections\\Aggregate\\AverageValues'                           => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Aggregate\\AverageValues',
    'Avax\\DataFoundation\\Collections\\Aggregate\\SumValues'                               => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Aggregate\\SumValues',
    'Avax\\DataFoundation\\Collections\\Transform\\MapValues'                               => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Transform\\MapValues',
    'Avax\\DataFoundation\\Collections\\Transform\\FilterValues'                            => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Transform\\FilterValues',
    'Avax\\DataFoundation\\Collections\\Search\\MatchTextFuzzily'                           => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\MatchTextFuzzily',
    'Avax\\DataFoundation\\Collections\\Search\\MatchTextPartially'                         => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\MatchTextPartially',
    'Avax\\DataFoundation\\Collections\\Search\\ContainsValue'                              => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\ContainsValue',
    'Avax\\DataFoundation\\Collections\\Search\\SearchValue'                                => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\SearchValue',
    'Avax\\DataFoundation\\Values\\Option\\Option'                                          => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Option\\Option',
    'Avax\\DataFoundation\\Values\\Option\\Some'                                            => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Option\\Some',
    'Avax\\DataFoundation\\Values\\Option\\None'                                            => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Option\\None',
    'Avax\\DataFoundation\\Values\\Result\\Result'                                          => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Result\\Result',
    'Avax\\DataFoundation\\Values\\Result\\Ok'                                              => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Result\\Success',
    'Avax\\DataFoundation\\Values\\Result\\Error'                                           => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Result\\Failure',
    'Avax\\DataFoundation\\Composites\\MapEntry\\MapEntry'                                  => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\MapEntry',
    'Avax\\DataFoundation\\Composites\\Pair\\Pair'                                          => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Pair',
    'Avax\\DataFoundation\\Composites\\Tuple\\Tuple2'                                       => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Tuple2',
    'Avax\\DataFoundation\\Composites\\Tuple\\Tuple3'                                       => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Tuple3',
    'Avax\\DataFoundation\\Composites\\Tuple\\Tuple4'                                       => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Tuple4',
    'Avax\\DataFoundation\\Composites\\Record\\Record'                                      => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Record',
    'Avax\\DataFoundation\\Composites\\Record\\RecordField'                                 => 'Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\RecordField',

    // DataShape ecosystem
    'Avax\\DataFoundation\\DataTransfer\\InspectDataShape\\InspectDataShape' => 'Avax\\Components\\Data\\System\\Capabilities\\DataShape\\InspectDataShape',
    'Avax\\DataFoundation\\DataTransfer\\InspectDataShape\\DataField'                       => 'Avax\\Components\\Data\\System\\Capabilities\\DataShape\\DataField',
    'Avax\\DataFoundation\\DataTransfer\\InspectDataShape\\DataFieldType'                   => 'Avax\\Components\\Data\\System\\Capabilities\\DataShape\\DataFieldType',

    // ObjectReading ecosystem
    'Avax\\DataFoundation\\DataTransfer\\ObjectReading\\ReadDataObject'                     => 'Avax\\Components\\Data\\System\\Capabilities\\ObjectReading\\ReadDataObject',
    'Avax\\DataFoundation\\DataTransfer\\ObjectReading\\NormalizeDataObjectValue' => 'Avax\\Components\\Data\\System\\Capabilities\\ObjectReading\\NormalizeDataObjectValue',

    // Serialization flow ecosystem
    'Avax\\DataFoundation\\DataTransfer\\SerializeDataObject\\SerializeDataObject'          => 'Avax\\Components\\Data\\System\\Flows\\SerializeDataObject\\SerializeDataObject',
    'Avax\\DataFoundation\\DataTransfer\\SerializeDataObject\\ConvertDataObjectToArray'     => 'Avax\\Components\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToArray',
    'Avax\\DataFoundation\\DataTransfer\\SerializeDataObject\\ConvertDataObjectToJson'      => 'Avax\\Components\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToJson',
    'Avax\\DataFoundation\\DataTransfer\\SerializeDataObject\\ConvertDataObjectToStdClass' => 'Avax\\Components\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToStdClass',
    'Avax\\DataFoundation\\DataTransfer\\SerializeDataObject\\ConvertDataObjectToJsonApi'   => 'Avax\\Components\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToJsonApi',

    'Avax\\Text\\Text'                                                => 'Avax\\Components\\Text\\System\\PublicSurface\\Text',
    'Avax\\DataHandling\\ObjectHandling\\DTO\\DTOValidationException' => 'Avax\\DataFoundation\\ObjectHandling\\DTO\\DTOValidationException',
    'Avax\\DataHandling\\Validation\\Attributes\\Rules\\EmailRule'    => 'Avax\\DataFoundation\\Validation\\Attributes\\Rules\\EmailRule',
    'Avax\\DataHandling\\Validation\\Attributes\\Rules\\MinLengthRule' => 'Avax\\DataFoundation\\Validation\\Attributes\\Rules\\MinLengthRule',

    // Router compat aliases - only core interfaces exist at new location
    // Other Router aliases removed: old component structure was replaced by HTTP/Router suite
    Router::class                                                 => \Avax\Components\HTTP\Router\System\PublicSurface\Router::class,
    RouterInterface::class                                        => \Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface::class,
    RouterRuntimeInterface::class                                 => \Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface::class,

    'Avax\\Database\\Database'                                                                  => 'Avax\\Components\\DataStack\\Database\\Database',
    'Avax\\Database\\EntityManager'                                                             => 'Avax\\Components\\DataStack\\Database\\EntityManager',
    'Avax\\Database\\Migrations'                                                                => 'Avax\\Components\\DataStack\\Database\\Migrations',
    'Avax\\Database\\Query'                                                                     => 'Avax\\Components\\DataStack\\Database\\Query',
    'Avax\\Database\\Schema'                                                                    => 'Avax\\Components\\DataStack\\Database\\Schema',
    'Avax\\Database\\Telemetry'                                                                 => 'Avax\\Components\\DataStack\\Database\\Telemetry',
    'Avax\\Database\\Transactions'                                                              => 'Avax\\Components\\DataStack\\Database\\Transactions',
    Connections::class                                            => \Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections::class,
    ConnectionPool::class                                         => \Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ConnectionPool::class,
    ColumnDefinition::class                                       => \Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Column\DSL\ColumnDefinition::class,
    ColumnSQLRenderer::class                                      => \Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Column\Render\ColumnSQLRenderer::class,
    Blueprint::class                                              => \Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Table\Blueprint::class,
    Column::class                                                 => \Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Column::class,
    Entity::class                                                 => \Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Entity::class,
    GeneratedValue::class                                         => \Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\GeneratedValue::class,
    Id::class                                                     => \Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Id::class,
    Table::class                                                  => \Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Table::class,
    EntityRepository::class                                       => \Avax\Components\DataStack\Database\System\Capabilities\ORM\Repositories\EntityRepository::class,
    QueryBuilder::class                                           => \Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder::class,
    QueryException::class                                         => \Avax\Components\DataStack\Database\System\Capabilities\Query\Exceptions\QueryException::class,
    MySQLGrammar::class                                           => \Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\MySQLGrammar::class,
    TransactionException::class                                   => \Avax\Components\DataStack\Database\System\Capabilities\Transactions\Exceptions\TransactionException::class,
    \Avax\Tests\Foundation\Database\Unit\Attributes\Column::class => \Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Column::class,
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\ServerRequest'                        => 'Avax\\Components\\Request\\System\\PublicSurface\\ServerRequest',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestInit'                          => 'Avax\\Components\\Request\\System\\Capabilities\\RequestInit',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\ServerInit'                           => 'Avax\\Components\\Request\\System\\Capabilities\\ServerInit',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestHeaders\\RequestHeaders'       => 'Avax\\Components\\Request\\System\\Capabilities\\RequestHeaders\\RequestHeaders',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\RequestBody'             => 'Avax\\Components\\Request\\System\\Capabilities\\RequestBody\\RequestBody',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\ParsedBody'              => 'Avax\\Components\\Request\\System\\Capabilities\\RequestBody\\ParsedBody',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestCookies\\RequestCookies'       => 'Avax\\Components\\Request\\System\\Capabilities\\RequestCookies\\RequestCookies',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\UploadedFiles\\UploadedFiles'         => 'Avax\\Components\\Request\\System\\Capabilities\\UploadedFiles\\UploadedFiles',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestAttributes\\RequestAttributes' => 'Avax\\Components\\Request\\System\\Capabilities\\RequestAttributes\\RequestAttributes',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestTarget\\ReadRequestTarget'     => 'Avax\\Components\\Request\\System\\Capabilities\\RequestTarget\\ReadRequestTarget',
    'Avax\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestedInputs\\RequestedInputs'     => 'Avax\\Components\\Request\\System\\Capabilities\\RequestedInputs\\RequestedInputs',
    'Avax\\HTTP\\Request\\Request'                                                              => 'Avax\\Components\\HTTP\\Request\\Request',
    'Avax\\HTTP\\Request\\RequestDtoFactory'                                                    => 'Avax\\HTTP\\Request\\RequestDtoFactory',
    'Avax\\Logging\\LoggerFactory'                                                              => 'Avax\\Components\\Logging\\System\\Configuration\\RegisterLogging',
    'Avax\\Cache\\Cache'                                                                        => 'Avax\\Components\\Application\\Cache\\System\\PublicSurface\\Cache',
    'Avax\\Config\\Architecture\\DDD\\AppPath'                                                  => 'Avax\\Components\\Config\\System\\Capabilities\\Architecture\\AppPath',
    'Avax\\DumpDebugger\\DumpDebugger'                                                          => 'Avax\\Components\\DumpDebugger\\System\\PublicSurface\\Dump',
    'Avax\\Components\\Application\\Container\\System\\PublicSurface\\Container'                => 'Avax\\Components\\Application\\Container\\System\\Container',
    HttpKernel::class                                             => \Avax\Components\HTTP\System\Capabilities\Kernel\HttpKernel::class,
    AppKernel::class                                              => \Avax\Components\HTTP\System\Capabilities\Kernel\AppKernel::class,
    ResolveRouteFromHttpRequest::class                            => \Avax\Components\HTTP\System\Flows\Routing\ResolveRouteFromHttpRequest::class,
    'Avax\\HTTP\\Response\\Response'                                                            => 'Avax\\Components\\Response\\System\\PublicSurface\\Response',
    'Avax\\HTTP\\Response\\ResponseEmitter'                                                     => 'Avax\\Components\\Response\\System\\Capabilities\\ResponseEmitter',
    'Avax\\HTTP\\Response\\Capabilities\\Message\\ResponseMessage'                              => 'Avax\\Components\\Response\\System\\Capabilities\\Message\\ResponseMessage',
    'Avax\\HTTP\\Response\\Capabilities\\Headers\\ResponseHeaders'                              => 'Avax\\Components\\Response\\System\\Capabilities\\Headers\\ResponseHeaders',
    'Avax\\HTTP\\Response\\Capabilities\\Body\\ResponseBody'                                    => 'Avax\\Components\\Response\\System\\Capabilities\\Body\\ResponseBody',
    'Avax\\HTTP\\Response\\Capabilities\\Caching\\CacheControl'                                 => 'Avax\\Components\\Response\\System\\Capabilities\\Caching\\CacheControl',
    'Avax\\HTTP\\Response\\Capabilities\\Caching\\Etag'                                         => 'Avax\\Components\\Response\\System\\Capabilities\\Caching\\Etag',
    'Avax\\HTTP\\Response\\Capabilities\\Caching\\LastModified'                                 => 'Avax\\Components\\Response\\System\\Capabilities\\Caching\\LastModified',
    'Avax\\HTTP\\Response\\Flows\\BuildResponse\\BuildResponse'                                 => 'Avax\\Components\\Response\\System\\Flows\\BuildResponse\\BuildResponse',
    'Avax\\HTTP\\Response\\Flows\\EmitResponse\\EmitResponse'                                   => 'Avax\\Components\\Response\\System\\Flows\\EmitResponse\\EmitResponse',
    'Avax\\HTTP\\RouterBootstrapper'                                                            => 'Avax\\Components\\HTTP\\System\\Configuration\\RouterBootstrapper',
    Session::class                                                => \Avax\Components\HTTP\Session\System\PublicSurface\Session::class,
    HttpContext::class                                            => \Avax\Components\HTTP\Context\System\PublicSurface\HttpContext::class,
    View::class                                                   => \Avax\Components\Presentation\View\System\PublicSurface\View::class,
    Filesystem::class                                             => \Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem::class,
    Config::class                                                 => \Avax\Components\Application\Config\System\PublicSurface\Config::class,
    \Avax\Container\ContainerInterface::class => ContainerInterface::class,
];

foreach ($classAliases as $alias => $target) {
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
