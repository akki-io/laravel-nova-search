<?php

namespace AkkiIo\LaravelNovaSearch;

use Closure;
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
            || ! empty(static::$searchRelations)
            || ! empty(static::$searchRelationsConcatenation)
            || ! empty(static::$searchRelationsMatchingAny);
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
     * Get the searchable relations concatenation columns for the resource.
     *
     * @return array
     */
    public static function searchableRelationsConcatenationColumns()
    {
        return static::$searchRelationsConcatenation ?? [];
    }

    /**
     * Get the searchable relations matching any columns for the resource.
     *
     * @return array
     */
    public static function searchableRelationsMatchingAnyColumns()
    {
        return static::$searchRelationsMatchingAny ?? [];
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
            static::applyRelationConcatenationColumnsSearch($query, $search);
            static::applyRelationMatchingAnyColumnsSearch($query, $search);
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
        $tokens = collect(explode(' ', $search));

        foreach (static::searchableMatchingAnyColumns() as $columns) {
            $tokens->each(function ($token) use ($query, $model, $columns) {
                $query->orWhere($model->qualifyColumn($columns), static::likeOperator($query), '%'.$token.'%');
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
     * Apply the relationship column concatenation search query to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applyRelationConcatenationColumnsSearch($query, $search)
    {
        foreach (static::searchableRelationsConcatenationColumns() as $relation => $columns) {
            $query->orWhereHas($relation, function ($query) use ($columns, $search) {
                $query->where(static::searchRelationConcatenationQueryApplier($columns, $search));
            });
        }

        return $query;
    }

    /**
     * Returns a Closure that applies a search query for a given concatenated columns.
     *
     * @param array $columns
     * @param string $search
     * @return Closure
     */
    protected static function searchRelationConcatenationQueryApplier(array $columns, string $search)
    {
        return function ($query) use ($columns, $search) {
            $model = $query->getModel();
            $operator = static::likeOperator($query);

            foreach ($columns as $items) {
                if (is_array($items)) {
                    $query->orWhereRaw(
                        static::concatCondition($query, $items).' '.$operator.' ?',
                        ['%'.$search.'%']
                    );
                } else {
                    $query->orWhere($model->qualifyColumn($items), $operator, '%'.$search.'%');
                }
            }
        };
    }

    /**
     * Apply the relationship matching any column search query to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applyRelationMatchingAnyColumnsSearch($query, $search)
    {
        foreach (static::searchableRelationsMatchingAnyColumns() as $relation => $columns) {
            $query->orWhereHas($relation, function ($query) use ($columns, $search) {
                $query->where(static::searchRelationMatchingAnyQueryApplier($columns, $search));
            });
        }

        return $query;
    }

    /**
     * Returns a Closure that applies a matching any search query for a given columns.
     *
     * @param array $columns
     * @param string $search
     * @return Closure
     */
    protected static function searchRelationMatchingAnyQueryApplier(array $columns, string $search)
    {
        return function ($query) use ($columns, $search) {
            $model = $query->getModel();
            $operator = static::likeOperator($query);
            $tokens = collect(explode(' ', $search));

            foreach ($columns as $items) {
                $tokens->each(function ($token) use ($query, $model, $items, $operator) {
                    $query->orWhere($model->qualifyColumn($items), $operator, '%'.$token.'%');
                });
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

        // Concat with COALESCE to turn possible NULL values into empty strings.
        foreach ($columns as $idx => $column) {
            $columns[$idx] = sprintf("COALESCE(%s, '')", $column);
        }

        return 'CONCAT('.implode(", ' ', ", $columns).') ';
    }
}
