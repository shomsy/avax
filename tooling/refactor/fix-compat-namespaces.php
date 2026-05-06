<?php

$file = 'components/compat.php';
$content = file_get_contents($file);

$map = [
    'Avax\Application\Config\Config' => 'Avax\Components\Application\Config\System\PublicSurface\Config',
    'Avax\Application\Filesystem\Filesystem' => 'Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem',
    'Avax\Database\System\Capabilities\Connections\Connections' => 'Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections',
    'Avax\Database\System\Capabilities\Connections\Pools\ConnectionPool' => 'Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ConnectionPool',
    'Avax\Database\System\Capabilities\Migrations\Design\Column\DSL\ColumnDefinition' => 'Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Column\DSL\ColumnDefinition',
    'Avax\Database\System\Capabilities\Migrations\Design\Column\Render\ColumnSQLRenderer' => 'Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Column\Render\ColumnSQLRenderer',
    'Avax\Database\System\Capabilities\Migrations\Design\Table\Blueprint' => 'Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Table\Blueprint',
    'Avax\Database\System\Capabilities\ORM\Attributes\Column' => 'Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Column',
    'Avax\Database\System\Capabilities\ORM\Attributes\Entity' => 'Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Entity',
    'Avax\Database\System\Capabilities\ORM\Attributes\GeneratedValue' => 'Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\GeneratedValue',
    'Avax\Database\System\Capabilities\ORM\Attributes\Id' => 'Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Id',
    'Avax\Database\System\Capabilities\ORM\Attributes\Table' => 'Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Table',
    'Avax\Database\System\Capabilities\ORM\Repositories\EntityRepository' => 'Avax\Components\DataStack\Database\System\Capabilities\ORM\Repositories\EntityRepository',
    'Avax\Database\System\Capabilities\Query\Builder\QueryBuilder' => 'Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder',
    'Avax\Database\System\Capabilities\Query\Exceptions\QueryException' => 'Avax\Components\DataStack\Database\System\Capabilities\Query\Exceptions\QueryException',
    'Avax\Database\System\Capabilities\Query\Grammar\MySQLGrammar' => 'Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\MySQLGrammar',
    'Avax\Database\System\Capabilities\Transactions\Exceptions\TransactionException' => 'Avax\Components\DataStack\Database\System\Capabilities\Transactions\Exceptions\TransactionException',
    'Avax\DataHandling\DataTransfer' => 'Avax\Components\DataStack\Data\System\Capabilities\DataTransfer',
    'Avax\DataHandling\ObjectHandling' => 'Avax\Components\DataStack\Data\System\Capabilities\ObjectHandling',
    'Avax\HTTP\AppKernel' => 'Avax\Components\HTTP\System\Capabilities\Kernel\AppKernel',
    'Avax\HTTP\HttpKernel' => 'Avax\Components\HTTP\System\Capabilities\Kernel\HttpKernel',
    'Avax\HTTP\Context\HttpContext' => 'Avax\Components\HTTP\Context\System\PublicSurface\HttpContext',
    'Avax\HTTP\Router\Router' => 'Avax\Components\HTTP\Router\System\PublicSurface\Router',
    'Avax\HTTP\Session\Session' => 'Avax\Components\HTTP\Session\System\PublicSurface\Session',
    'Avax\Presentation\View\View' => 'Avax\Components\Presentation\View\System\PublicSurface\View',
];

foreach ($map as $old => $new) {
    $content = str_replace($old, $new, $content);
}

file_put_contents($file, $content);
echo "Fixed compat.php\n";
