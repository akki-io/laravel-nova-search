<?php

namespace AkkiIo\LaravelNovaSearch;

use Closure;
use Illuminate\Support\Str;
use function implode;
use function is_array;

trait LaravelNovaSearchable
{
    /**
     * Determine if this resource is searchable.
     *
     * @return bool
     */
    public static function searchable()
    {
        return parent::searchable()
            || ! empty(static::$searchConcatenation)
            || ! empty(static::$searchMatchingAny)
            || ! empty(static::$searchRelations);
    }

    /**
     * Get the searchable concatenation columns for the resource.
     *
     * @return array
     */
    public static function searchableConcatenationColumns()
    {
        return static::$searchConcatenation ?? [];
    }

    /**
     * Get the searchable matching any columns for the resource.
     *
     * @return array
     */
    public static function searchableMatchingAnyColumns()
    {
        return static::$searchMatchingAny ?? [];
    }

    /**
     * Get the searchable relations columns for the resource.
     *
     * @return array
     */
    public static function searchableRelationsColumns()
    {
        return static::$searchRelations ?? [];
    }

    /**
     * Apply the search query to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applySearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            parent::applySearch($query, $search);
            static::applyConcatenationColumnsSearch($query, $search);
            static::applyMatchingAnyColumnsSearch($query, $search);
            static::applyRelationSearch($query, $search);
        });
    }

    /**
     * Apply the concatenation column search query to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applyConcatenationColumnsSearch($query, $search)
    {
        $model = $query->getModel();

        foreach (static::searchableConcatenationColumns() as $columns) {
            if (is_array($columns)) {
                $query->orWhereRaw(
                    static::concatCondition($query, $columns).' '.static::likeOperator($query).' ?',
                    ['%'.$search.'%']
                );
            } else {
                $query->orWhere($model->qualifyColumn($columns), static::likeOperator($query), '%'.$search.'%');
            }
        }

        return $query;
    }

    /**
     * Apply the concatenation column search query to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applyMatchingAnyColumnsSearch($query, $search)
    {
        $model = $query->getModel();

        foreach (static::searchableMatchingAnyColumns() as $columns) {
            Str::of($search)
                ->explode(' ')
                ->each(function ($item) use ($query, $model, $columns) {
                    $query->orWhere($model->qualifyColumn($columns), static::likeOperator($query), '%'.$item.'%');
                });
        }

        return $query;
    }

    /**
     * Apply the relationship search query to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applyRelationSearch($query, $search)
    {
        foreach (static::searchableRelationsColumns() as $relation => $columns) {
            $query->orWhereHas($relation, function ($query) use ($columns, $search) {
                $query->where(static::searchRelationQueryApplier($columns, $search));
            });
        }

        return $query;
    }

    /**
     * Returns a Closure that applies a search query for a given columns.
     *
     * @param  array $columns
     * @param  string $search
     * @return Closure
     */
    protected static function searchRelationQueryApplier(array $columns, string $search)
    {
        return function ($query) use ($columns, $search) {
            $model = $query->getModel();
            $operator = static::likeOperator($query);

            foreach ($columns as $column) {
                $query->orWhere($model->qualifyColumn($column), $operator, '%'.$search.'%');
            }
        };
    }

    /**
     * Resolve the query operator.
     *
     * @param \Illuminate\Database\Eloquent\Builder  $query
     * @return string
     */
    protected static function likeOperator($query)
    {
        if ($query->getModel()->getConnection()->getDriverName() === 'pgsql') {
            return 'ILIKE';
        }

        return 'LIKE';
    }

    /**
     * Resolve the concat condition.
     *
     * @param $query
     * @param $columns
     * @return string
     */
    protected static function concatCondition($query, $columns)
    {
        if ($query->getModel()->getConnection()->getDriverName() === 'sqlite') {
            return implode(" || ' ' || ", $columns);
        }

        return 'CONCAT('.implode(", ' ', ", $columns).') ';
    }
}
