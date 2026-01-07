<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\CurrencySetting;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share categories with all views
        View::composer('layouts.app', function ($view) {
            $categories = cache()->remember('categories.active.root.header', 3600, function () {
                return Category::active()->root()->with('children')->orderBy('sort_order')->orderBy('name')->get();
            });
            $view->with('headerCategories', $categories);
        });

        // Share currency settings with all views
        View::composer('*', function ($view) {
            $currency = cache()->remember('currency_settings.active', 3600, function () {
                return CurrencySetting::getActive() ?? CurrencySetting::getOrCreateSetting();
            });
            $view->with('currency', $currency);
        });

        // Blade directive for currency formatting
        Blade::directive('currency', function ($expression) {
            return "<?php echo \App\Models\CurrencySetting::format($expression); ?>";
        });

        // Optimize query performance
        if (config('app.debug') === false) {
            \Illuminate\Support\Facades\DB::listen(function ($query) {
                if ($query->time > 100) { // Log slow queries (>100ms)
                    \Log::warning('Slow query detected', [
                        'sql' => $query->sql,
                        'time' => $query->time,
                    ]);
                }
            });
        }
    }
}
