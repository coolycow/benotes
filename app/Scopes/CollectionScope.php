<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CollectionScope implements Scope
{
    /**
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $builder
            ->where('collections.user_id', Auth::id())
            ->orWhereHas('shares', function (Builder $query) {
                $query->where('guest_id', Auth::id());
            });
    }
}
