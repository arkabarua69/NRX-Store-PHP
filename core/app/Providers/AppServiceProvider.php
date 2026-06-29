<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\Page;
use App\Constants\Status;
use App\Services\PWAService;
use App\Livewire\CustomHandleRequests;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\Livewire\Mechanisms\HandleRequests\HandleRequests::class, CustomHandleRequests::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureStorageDirectories();

        $viewShare['settings'] = gs();
        view()->share($viewShare);

        try {
            View::composer('layouts.app', function ($view) {
                $pages = Page::where('status', Status::ACTIVE)
                    ->orderBy('order_column')
                    ->get();
                $menus = Menu::where('status', Status::ACTIVE)
                    ->orderBy('order_column')
                    ->get();
                $view->with('pages', $pages);
                $view->with('menus', $menus);
            });
        } catch (\Exception $e) {
            //throw $th;
        }

        Blade::directive('PWA', function () {
            return (new PWAService)->render();
        });

        if (app()->isProduction() || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }

        // Sync APP_URL with the actual public-facing host (handles ngrok, reverse proxies, etc.)
        // Priority: X-Forwarded-Host (set by ngrok/proxy) > actual request host
        $forwardedHost = request()->server('HTTP_X_FORWARDED_HOST');
        $forwardedProto = request()->server('HTTP_X_FORWARDED_PROTO');
        $configuredUrl = config('app.url');
        $configuredPath = rtrim(parse_url($configuredUrl, PHP_URL_PATH) ?? '', '/');

        if ($forwardedHost) {
            // Behind a proxy/ngrok — use the forwarded host
            $scheme = $forwardedProto ?: 'https';
            $dynamicUrl = $scheme . '://' . $forwardedHost . $configuredPath;
            config(['app.url' => $dynamicUrl]);
            URL::forceRootUrl($dynamicUrl);

            // Also update filesystem disk URLs so media images use correct host
            config(['filesystems.disks.public.url' => $dynamicUrl . '/uploads']);
            config(['filesystems.disks.media.url' => $dynamicUrl . '/uploads/media']);
        }

        Model::preventLazyLoading(! app()->isProduction());
    }

    protected function ensureStorageDirectories(): void
    {
        $dirs = [
            storage_path('app/livewire-tmp'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
            public_path('uploads'),
            public_path('uploads/settings'),
            public_path('uploads/media'),
        ];

        foreach ($dirs as $dir) {
            if (!File::isDirectory($dir)) {
                @File::makeDirectory($dir, 0775, true, true);
            }
        }
    }
}
