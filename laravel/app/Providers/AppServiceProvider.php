<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->bind('path.public', function() {
        //     return base_path() . '/../layanan-fst.uinjakarta.id';
        // });
        // $this->app->usePublicPath(__DIR__.'/../../layanan-fst.uinjakarta.id');
        /* old version code
        $this->app->bind('path.public', fn() => base_path() . '/public_html';);
        */
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
