<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Projections;

final class TypedResult
{
    /**
     * @template T of object
     *
     * @param  class-string<T>  $className
     * @param  array<array-key, mixed>  $rows
     * @return list<T>
     */
    public static function fromRows(string $className, array $rows): array
    {
        $resultMapper = new ResultMapper(className: $className);

        return array_map(
            callback: static fn (array $row): object => $resultMapper->map(row: $row),
            array   : $rows,
        );
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $className
     * @param  array<array-key, mixed>  $row
     * @return T|null
     */
    public static function fromRow(string $className, array $row) : object|null
    {
        if ($row === []) {
            return null;
        }

        $resultMapper = new ResultMapper(className: $className);

        return $resultMapper->map(row: $row);
    }
}
