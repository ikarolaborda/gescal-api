<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ExcludeSoftDeletedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNull($model->getQualifiedDeletedAtColumn());
    }

    /**
     * Extend the query builder with helper methods.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withTrashed', function (Builder $query) {
            return $query->withoutGlobalScope($this);
        });

        $builder->macro('onlyTrashed', function (Builder $query) {
            $model = $query->getModel();

            return $query->withoutGlobalScope($this)
                ->whereNotNull($model->getQualifiedDeletedAtColumn());
        });
    }
}
