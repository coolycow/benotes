<?php

namespace App\Providers;

use App\Repositories\CollectionRepository;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\PostTagRepositoryInterface;
use App\Repositories\Contracts\ShareRepositoryInterface;
use App\Repositories\Contracts\TagRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\PostRepository;
use App\Repositories\PostTagRepository;
use App\Repositories\ShareRepository;
use App\Repositories\TagRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        CollectionRepositoryInterface::class => CollectionRepository::class,
        PostRepositoryInterface::class => PostRepository::class,
        TagRepositoryInterface::class => TagRepository::class,
        PostTagRepositoryInterface::class => PostTagRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
        ShareRepositoryInterface::class => ShareRepository::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
