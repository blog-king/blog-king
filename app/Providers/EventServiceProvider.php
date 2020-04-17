<?php

namespace App\Providers;

use App\Events\ConcernCreated;
use App\Events\PostDeleted;
use App\Events\PostUpdated;
use App\Listeners\ConcernCreatedListener;
use App\Listeners\PostDeleteCacheListener;
use App\Listeners\PostUpdatedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        //文章更新监听
        PostUpdated::class => [
            PostDeleteCacheListener::class,
            PostUpdatedListener::class,
        ],

        //文章删除监听
        PostDeleted::class => [
            PostDeleteCacheListener::class,
        ],

        //用户订阅监听
        ConcernCreated::class => [
            ConcernCreatedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
