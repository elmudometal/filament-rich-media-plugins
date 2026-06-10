<?php

namespace ElmudoDev\FilamentRichMediaPlugins;

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
}
