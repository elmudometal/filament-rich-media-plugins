<?php

declare(strict_types=1);

use ElmudoDev\FilamentRichMediaPlugins\Extensions\ImageExtension;
use ElmudoDev\FilamentRichMediaPlugins\ImagePlugin;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\RichEditorTool;

test('plugin can be instantiated', function () {
    $plugin = ImagePlugin::make();
    expect($plugin)->toBeInstanceOf(ImagePlugin::class);
});

test('plugin returns correct PHP extensions', function () {
    $plugin = ImagePlugin::make();
    $extensions = $plugin->getTipTapPhpExtensions();

    expect($extensions)->toBeArray()
        ->and(count($extensions))->toBe(1)
        ->and($extensions[0])->toBeInstanceOf(ImageExtension::class);
});

test('plugin returns correct JS extensions', function () {
    $plugin = ImagePlugin::make();
    $extensions = $plugin->getTipTapJsExtensions();

    expect($extensions)->toBeArray()
        ->and(count($extensions))->toBe(1)
        ->and($extensions[0])->toContain('rich-media-plugins');
});

test('plugin returns editor tools', function () {
    $plugin = ImagePlugin::make();
    $tools = $plugin->getEditorTools();

    expect($tools)->toBeArray()
        ->and(count($tools))->toBe(1)
        ->and($tools[0])->toBeInstanceOf(RichEditorTool::class)
        ->and($tools[0]->getName())->toBe('image');
});

test('plugin returns editor actions', function () {
    $plugin = ImagePlugin::make();
    $actions = $plugin->getEditorActions();

    expect($actions)->toBeArray()
        ->and(count($actions))->toBe(1)
        ->and($actions[0])->toBeInstanceOf(Action::class)
        ->and($actions[0]->getName())->toBe('image');
});
