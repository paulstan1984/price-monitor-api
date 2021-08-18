<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //

        Validator::extend('numericarray', function ($attribute, $value, $parameters) {

            if(!is_array($value)) {
                return false;
            }

            foreach ($value as $v) {
                if (!is_int($v)) return false;
            }
            return true;
        });
    }
}
