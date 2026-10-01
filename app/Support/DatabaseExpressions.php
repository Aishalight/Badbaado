<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;

/**
 * Raw SQL fragments that have to be spelled differently per driver.
 *
 * The application ships sqlite, mysql and pgsql connections, so date bucketing
 * and free-text search over JSON columns cannot assume one dialect. Only
 * literal column names may be passed in — never user input.
 */
final class DatabaseExpressions
{
    /**
     * Truncates a timestamp column to a calendar day for GROUP BY use.
     */
    public static function day(EloquentBuilder|Builder $query, string $column): string
    {
        return match (self::driver($query)) {
            'pgsql' => "DATE_TRUNC('day', {$column})",
            'sqlsrv' => "CAST({$column} AS date)",
            default => "DATE({$column})",
        };
    }

    /**
     * Projects a timestamp column as a sortable 'Y-m' string, for trend charts.
     */
    public static function yearMonth(EloquentBuilder|Builder $query, string $column): string
    {
        return match (self::driver($query)) {
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            'mysql', 'mariadb' => "DATE_FORMAT({$column}, '%Y-%m')",
            'sqlsrv' => "FORMAT({$column}, 'yyyy-MM')",
            default => "strftime('%Y-%m', {$column})",
        };
    }

    /**
     * Projects a JSON column as searchable text. Postgres cannot LIKE a json or
     * jsonb column directly, so the cast is required there.
     */
    public static function jsonAsText(EloquentBuilder|Builder $query, string $column): string
    {
        return match (self::driver($query)) {
            'pgsql' => "{$column}::text",
            'mysql', 'mariadb' => "CAST({$column} AS CHAR)",
            default => $column,
        };
    }

    private static function driver(EloquentBuilder|Builder $query): string
    {
        return $query->getConnection()->getDriverName();
    }
}
