<?php

namespace App\Observers;

use App\Models\Collection;
use Illuminate\Support\Str;

class CollectionObserver
{
    /**
     * Handle the Collection "created" event.
     */
    public function created(Collection $collection): void
    {
        //
    }

    /**
     * Handle the Collection "updated" event.
     */
    public function updated(Collection $collection): void
    {
        //
    }

    /**
     * Handle the Collection "deleted" event.
     */
    public function deleted(Collection $collection): void
    {
        //
    }

    /**
     * Handle the Collection "restored" event.
     */
    public function restored(Collection $collection): void
    {
        //
    }

    /**
     * Handle the Collection "force deleted" event.
     */
    public function forceDeleted(Collection $collection): void
    {
        //
    }

    /**
     * Handle the Collection "created" event.
     */
    public function saving(Collection $collection): void
    {
        $collection->name = Str::limit($collection->name, 255, '');
    }
}
