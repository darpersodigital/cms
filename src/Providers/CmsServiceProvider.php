<?php

namespace Darpersodigital\Cms\Providers;
use Darpersodigital\Cms\Services\DatabaseServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

use Artisan;
use Schema;
use Auth;
use DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'darpersocms');

        // Controllers

        $this->app['router']->aliasMiddleware('admin', \Darpersodigital\Cms\Middlewares\AdminMiddleware::class);
        $this->app->singleton(DatabaseServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $this->publishes([__DIR__ . '/../publishable/darperso' => config_path('/')], 'darperso');
        $this->publishes([__DIR__ . '/../publishable/test-env' => config_path('/')], 'test-env');

        if (!is_array($this->app['config']->get('cms_config'))) {
            $this->runInitialSetup();
        } elseif (is_array($this->app['config']->get('cms_config'))) {
            $activeConfig = $this->app['config']->get('cms_config');
            // if ($activeConfig['build_number'] < 2) {
            //     $this->updateToV2();
            //     Artisan::call('vendor:publish', [
            //         '--tag' => $activeConfig['id'],
            //         '--force' => true,
            //     ]);
            // }
        }
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    protected function runInitialSetup()
    {
        $databaseServiceProvider = $this->app->make(DatabaseServiceProvider::class);

        $databaseServiceProvider->generateDatabase();
        // Publish cms assets

        $files = ['CfMessage.php', 'ContactPage.php', 'ContactPageTranslation.php', 'HomePage.php', 'HomePageTranslation.php', 'SiteSetting.php', 'SiteSettingTranslation.php'];
        foreach ($files as $file) {
            File::copy(__DIR__ . "/../static/models/{$file}", base_path("app/Models/{$file}"));
        }
        File::copyDirectory(__DIR__ . '/../static/fontawesome', base_path('public/assets/fontawesome-cms'));
    }

    protected function updateToV2() {}
}
