<?php

namespace ElmudoDev\FilamentRichMediaPlugins;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ElmudoDev\FilamentRichMediaPlugins\Commands\FilamentRichMediaPluginsCommand;

class FilamentRichMediaPluginsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-rich-media-plugins')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_filament_rich_media_plugins_table')
            ->hasCommand(FilamentRichMediaPluginsCommand::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/rich-media-link-button.php',
            'rich-media-link-button'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../config/rich-media-image.php',
            'rich-media-image'
        );
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(
            __DIR__ . '/../lang',
            'rich-media-plugins'
        );

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/rich-media-link-button.php' => config_path('rich-media-link-button.php'),
                __DIR__ . '/../config/rich-media-image.php' => config_path('rich-media-image.php'),
            ], 'rich-media-plugins-config');

            $this->publishes([
                __DIR__ . '/../lang' => $this->app->langPath('vendor/rich-media-plugins'),
            ], 'rich-media-plugins-lang');

            $this->publishes([
                __DIR__ . '/../resources/dist/js' => public_path('vendor/filament-rich-media-plugins/js'),
                __DIR__ . '/../resources/css' => public_path('vendor/filament-rich-media-plugins/css'),
            ], 'rich-media-plugins-assets');
        }

        FilamentAsset::register([
            Js::make('rich-media-plugins/link-button', __DIR__ . '/../resources/dist/js/filament/rich-media-plugins/link-button.js')->loadedOnRequest(),
            Js::make('rich-media-plugins/image', __DIR__ . '/../resources/dist/js/filament/rich-media-plugins/image.js')->loadedOnRequest(),
            Css::make('rich-media-plugins', __DIR__ . '/../resources/css/filament-rich-media-plugins.css'),
        ], 'elmudo-dev/filament-rich-media-plugins');
    }
}
