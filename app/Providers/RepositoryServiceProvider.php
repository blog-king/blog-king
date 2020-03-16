<?php

namespace App\Providers;

use App\Repository\Interfaces\PostInterface;
use App\Repository\Interfaces\UserGithubInformationInterface;
use App\Repository\Interfaces\UserInterface;
use App\Repository\Repositories\PostRepository;
use App\Repository\Repositories\UserGithubInformationRepository;
use App\Repository\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(UserGithubInformationInterface::class, UserGithubInformationRepository::class);
        $this->app->bind(PostInterface::class, PostRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
