<?php

namespace W360\ImageStorage;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class ImageStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerResources();
        $this->registerPublishing();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'image-storage');


        // Register the main class to use with the facade
        $this->app->singleton('imageSt', function () {
            return new ImageService;
        });

        $this->callAfterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $this->registerBladeExtensions($bladeCompiler);
        });
    }

    /**
     * @param $model
     * @return bool
     */
    public static function bladeMethodWrapper($model)
    {
        return isset($model->images) && is_array($model->images) ? $model->images()->count() > 0 : isset($model->images->name);
    }

    /**
     * @param $bladeCompiler
     */
    protected function registerBladeExtensions($bladeCompiler)
    {
        $bladeCompiler->directive('hasimage', function ($arguments) {
            return "<?php if(\\W360\\ImageStorage\\ImageStorageServiceProvider::bladeMethodWrapper('hasImage', {$arguments})): ?>";
        });
        $bladeCompiler->directive('endhasimage', function () {
            return '<?php endif; ?>';
        });
    }

    /**
     * register resources
     */
    private function registerResources()
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/create_image_storages_table.php.stub' => $this->getMigrationFileName('create_image_storages_table.php'),
        ], 'migrations');
    }

    /**
     * register publishing
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('image-storage.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'migrations');
        }
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param $migrationFileName
     * @return string
     * @throws BindingResolutionException
     */
    protected function getMigrationFileName($migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path.'*_'.$migrationFileName);
            })
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
