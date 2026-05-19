<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Grammar;

enum DatabaseType: string
{
    case RELATIONAL = 'relational';
    case DOCUMENT = 'document';
    case KEY_VALUE = 'key_value';
    case SEARCH = 'search';
    case WIDE_COLUMN = 'wide_column';
    case GRAPH = 'graph';
    case COLUMNAR = 'columnar';
}
