<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class DataTablesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Request::macro('isDataTablesRequest', function () {
            return $this->ajax() && $this->has('draw');
        });

        Inertia::macro('dataTables', function ($resource) {
            if (request()->isDataTablesRequest()) {
                return $resource;
            }

            return Inertia::render(...func_get_args());
        });
    }
}
