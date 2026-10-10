<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class Search
{
    /**
     * Match any of the given columns against a free-text term. Wildcards in
     * the term are escaped so "50%" searches for the literal text.
     *
     * @param  Builder<*>  $query
     * @param  list<string>  $columns
     */
    public static function apply(Builder $query, ?string $term, array $columns): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        $query->where(function (Builder $query) use ($columns, $like) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', $like);
            }
        });
    }
}
