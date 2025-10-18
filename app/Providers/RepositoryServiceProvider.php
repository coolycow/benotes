<?php

namespace App\Providers;

use App\Repositories\CollectionRepository;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\PostTagRepositoryInterface;
use App\Repositories\Contracts\ShareRepositoryInterface;
use App\Repositories\Contracts\TagRepositoryInterface;
use App\Repositories\PostRepository;
use App\Repositories\PostTagRepository;
use App\Repositories\ShareRepository;
use App\Repositories\TagRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        CollectionRepositoryInterface::class => CollectionRepository::class,
        PostRepositoryInterface::class => PostRepository::class,
        TagRepositoryInterface::class => TagRepository::class,
        PostTagRepositoryInterface::class => PostTagRepository::class,
        ShareRepositoryInterface::class => ShareRepository::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
