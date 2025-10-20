<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TagScope implements Scope
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

        $builder->where('user_id', Auth::id())
            ->orWhereHas('posts', function (Builder $postQuery) {
                $postQuery->whereHas('collection', function (Builder $query) {
                    $query->whereHas('shares', function (Builder $query) {
                        $query->where('shares.user_id', Auth::id())
                            ->orWhere('shares.guest_id', Auth::id());
                    });
                });
            });

    }
}
