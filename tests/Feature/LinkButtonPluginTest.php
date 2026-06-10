<?php

declare(strict_types=1);

use ElmudoDev\FilamentRichMediaPlugins\Extensions\LinkButtonExtension;
use ElmudoDev\FilamentRichMediaPlugins\LinkButtonPlugin;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\RichEditorTool;

test('plugin can be instantiated', function () {
    $plugin = LinkButtonPlugin::make();
    expect($plugin)->toBeInstanceOf(LinkButtonPlugin::class);
});

test('plugin returns correct PHP extensions', function () {
    $plugin = LinkButtonPlugin::make();
    $extensions = $plugin->getTipTapPhpExtensions();

    expect($extensions)->toBeArray()
        ->and(count($extensions))->toBe(1)
        ->and($extensions[0])->toBeInstanceOf(LinkButtonExtension::class);
});

test('plugin returns correct JS extensions', function () {
    $plugin = LinkButtonPlugin::make();
    $extensions = $plugin->getTipTapJsExtensions();

    expect($extensions)->toBeArray()
        ->and(count($extensions))->toBe(1)
        ->and($extensions[0])->toContain('rich-media-plugins');
});

test('plugin returns editor tools', function () {
    $plugin = LinkButtonPlugin::make();
    $tools = $plugin->getEditorTools();

    expect($tools)->toBeArray()
        ->and(count($tools))->toBe(1)
        ->and($tools[0])->toBeInstanceOf(RichEditorTool::class)
        ->and($tools[0]->getName())->toBe('linkButton');
});

test('plugin returns editor actions', function () {
    $plugin = LinkButtonPlugin::make();
    $actions = $plugin->getEditorActions();

    expect($actions)->toBeArray()
        ->and(count($actions))->toBe(1)
        ->and($actions[0])->toBeInstanceOf(Action::class)
        ->and($actions[0]->getName())->toBe('linkButton');
});
