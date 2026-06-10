<?php

declare(strict_types=1);

namespace ElmudoDev\FilamentRichMediaPlugins;

use ElmudoDev\FilamentRichMediaPlugins\Extensions\LinkButtonExtension;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Tiptap\Core\Extension;

class LinkButtonPlugin implements RichContentPlugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @return array<Extension>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [
            new LinkButtonExtension,
        ];
    }

    /**
     * @return array<string>
     *
     * @throws Exception
     */
    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('rich-media-plugins/link-button', 'elmudo-dev/filament-rich-media-plugins'),
        ];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('linkButton')
                ->label(__('rich-media-plugins::link-button.heading'))
                ->icon(Heroicon::OutlinedLink)
                ->action(arguments: '{ href: $getEditor().isActive(\'linkButton\') ? $getEditor().getAttributes(\'linkButton\')?.href : $getEditor().getAttributes(\'link\')?.href, id: $getEditor().isActive(\'linkButton\') ? $getEditor().getAttributes(\'linkButton\')?.id : $getEditor().getAttributes(\'link\')?.id, target: $getEditor().isActive(\'linkButton\') ? $getEditor().getAttributes(\'linkButton\')?.target : $getEditor().getAttributes(\'link\')?.target, hreflang: $getEditor().isActive(\'linkButton\') ? $getEditor().getAttributes(\'linkButton\')?.hreflang : $getEditor().getAttributes(\'link\')?.hreflang, rel: $getEditor().isActive(\'linkButton\') ? $getEditor().getAttributes(\'linkButton\')?.rel : $getEditor().getAttributes(\'link\')?.rel, referrerpolicy: $getEditor().isActive(\'linkButton\') ? $getEditor().getAttributes(\'linkButton\')?.referrerpolicy : $getEditor().getAttributes(\'link\')?.referrerpolicy, dataAsButton: $getEditor().isActive(\'linkButton\') ? $getEditor().getAttributes(\'linkButton\')?.dataAsButton : $getEditor().getAttributes(\'link\')?.dataAsButton, dataButtonTheme: $getEditor().isActive(\'linkButton\') ? $getEditor().getAttributes(\'linkButton\')?.dataButtonTheme : $getEditor().getAttributes(\'link\')?.dataButtonTheme, dataLinkSource: $getEditor().isActive(\'linkButton\') ? $getEditor().getAttributes(\'linkButton\')?.dataLinkSource : $getEditor().getAttributes(\'link\')?.dataLinkSource }'),
        ];
    }

    /**
     * @return array<Action>
     *
     * @throws Exception
     */
    public function getEditorActions(): array
    {
        return [
            Action::make('linkButton')
                ->label(__('rich-media-plugins::link-button.heading'))
                ->modalHeading(__('rich-media-plugins::link-button.heading'))
                ->modalWidth(Width::Large)
                ->fillForm(function (array $arguments): array {
                    $href = $arguments['href'] ?? null;
                    $dataAsButton = $arguments['dataAsButton'] ?? null;
                    $isButton = $dataAsButton === 'true';

                    return [
                        'type_input' => ($arguments['dataLinkSource'] ?? null) === 'file',
                        'href' => ($arguments['dataLinkSource'] ?? null) === 'file' ? null : $href,
                        'href_file' => ($arguments['dataLinkSource'] ?? null) === 'file' ? $this->hrefToPublicDiskPath($href) : null,
                        'id' => $arguments['id'] ?? null,
                        'target' => $arguments['target'] ?? '',
                        'hreflang' => $arguments['hreflang'] ?? null,
                        'rel' => $arguments['rel'] ?? null,
                        'referrerpolicy' => $arguments['referrerpolicy'] ?? null,
                        'as_button' => $isButton,
                        'button_theme' => $arguments['dataButtonTheme'] ?? 'primary',
                    ];
                })
                ->schema($this->linkFormSchema())
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $resolvedHref = $this->resolveHrefFromFormData($data);

                    $isSingleCharacterSelection = ($arguments['editorSelection']['head'] ?? null) === ($arguments['editorSelection']['anchor'] ?? null);

                    if (blank($resolvedHref)) {
                        $component->runCommands(
                            [
                                ...($isSingleCharacterSelection ? [
                                    EditorCommand::make('extendMarkRange', arguments: ['linkButton']),
                                    EditorCommand::make('extendMarkRange', arguments: ['link']),
                                ] : []),
                                EditorCommand::make('unsetLinkButton'),
                                EditorCommand::make('unsetLink'),
                            ],
                            editorSelection: $arguments['editorSelection'],
                        );

                        return;
                    }

                    $payload = $this->buildLinkPayload($resolvedHref, $data);
                    $asButton = filter_var($data['as_button'] ?? false, FILTER_VALIDATE_BOOLEAN);

                    if ($asButton) {
                        $component->runCommands(
                            [
                                ...($isSingleCharacterSelection ? [
                                    EditorCommand::make('extendMarkRange', arguments: ['linkButton']),
                                    EditorCommand::make('extendMarkRange', arguments: ['link']),
                                ] : []),
                                EditorCommand::make('unsetLink'),
                                EditorCommand::make('setLinkButton', arguments: [$payload]),
                            ],
                            editorSelection: $arguments['editorSelection'],
                        );
                    } else {
                        $component->runCommands(
                            [
                                ...($isSingleCharacterSelection ? [
                                    EditorCommand::make('extendMarkRange', arguments: ['linkButton']),
                                    EditorCommand::make('extendMarkRange', arguments: ['link']),
                                ] : []),
                                EditorCommand::make('unsetLinkButton'),
                                EditorCommand::make('setLink', arguments: [$payload]),
                            ],
                            editorSelection: $arguments['editorSelection'],
                        );
                    }
                }),
        ];
    }

    /**
     * @return array<int, Component>
     */
    protected function linkFormSchema(): array
    {
        return [
            Grid::make(['md' => 3])
                ->schema([
                    Toggle::make('type_input')
                        ->label(__('rich-media-plugins::link-button.fields.is_file'))
                        ->columnSpanFull()
                        ->reactive(),
                    TextInput::make('href')
                        ->label(__('rich-media-plugins::link-button.fields.href'))
                        ->columnSpanFull()
                        ->validationAttribute('URL')
                        ->visible(fn (Get $get): bool => ! (bool) $get('type_input')),
                    FileUpload::make('href_file')
                        ->label(__('rich-media-plugins::link-button.fields.file'))
                        ->disk((string) config('rich-media-link-button.disk', 'local'))
                        ->directory((string) config('rich-media-link-button.directory', 'userfiles/files/'))
                        ->visibility('public')
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull()
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/zip',
                            'application/x-zip-compressed',
                            'image/*',
                            'video/*',
                            'audio/*',
                            'text/plain',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->visible(fn (Get $get): bool => (bool) $get('type_input'))
                        ->required(fn (Get $get): bool => (bool) $get('type_input')),
                    TextInput::make('id')
                        ->label(fn (): array|string => trans('richer-editor::richer-editor.link.id')),
                    Select::make('target')
                        ->label(fn (): array|string => trans('richer-editor::richer-editor.link.target.label'))
                        ->selectablePlaceholder(false)
                        ->options([
                            '' => trans('richer-editor::richer-editor.link.target.self'),
                            '_blank' => trans('richer-editor::richer-editor.link.target.new_window'),
                            '_parent' => trans('richer-editor::richer-editor.link.target.parent'),
                            '_top' => trans('richer-editor::richer-editor.link.target.top'),
                        ]),
                    TextInput::make('hreflang')
                        ->label(fn (): array|string => trans('richer-editor::richer-editor.link.hreflang')),
                    TextInput::make('rel')
                        ->label(fn (): array|string => trans('richer-editor::richer-editor.link.rel'))
                        ->columnSpanFull(),
                    TextInput::make('referrerpolicy')
                        ->label(fn (): array|string => trans('richer-editor::richer-editor.link.referrerpolicy'))
                        ->columnSpanFull(),
                    Toggle::make('as_button')
                        ->label(__('rich-media-plugins::link-button.fields.as_button'))
                        ->reactive()
                        ->hidden(fn (): bool => (bool) config('rich-media-link-button.disable_link_as_button', false))
                        ->dehydratedWhenHidden()
                        ->default(false),
                    Radio::make('button_theme')
                        ->columnSpanFull()
                        ->columns(2)
                        ->visible(fn (Get $get): bool => (bool) $get('as_button'))
                        ->options([
                            'primary' => __('rich-media-plugins::link-button.button_theme.primary'),
                            'secondary' => __('rich-media-plugins::link-button.button_theme.secondary'),
                            'tertiary' => __('rich-media-plugins::link-button.button_theme.tertiary'),
                            'accent' => __('rich-media-plugins::link-button.button_theme.accent'),
                        ])
                        ->default('primary'),
                ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveHrefFromFormData(array $data): ?string
    {
        if (! empty($data['type_input'])) {
            /** @var string|null $path */
            $path = $data['href_file'] ?? null;
            if (blank($path)) {
                return null;
            }

            return Storage::disk((string) config('rich-media-link-button.disk', 'local'))->url($path);
        }

        /** @var string|null $href */
        $href = $data['href'] ?? null;

        return filled($href) ? $href : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function buildLinkPayload(string $href, array $data): array
    {
        $asButton = ! empty($data['as_button']) && ! config('rich-media-link-button.disable_link_as_button', false);

        $payload = [
            'href' => $href,
            'target' => $data['target'] ?? '',
            'dataLinkSource' => ! empty($data['type_input']) ? 'file' : 'url',
            'dataAsButton' => $asButton ? 'true' : null,
            'dataButtonTheme' => $asButton ? (string) ($data['button_theme'] ?? 'primary') : null,
        ];

        foreach (['id', 'hreflang', 'rel', 'referrerpolicy'] as $key) {
            $value = $data[$key] ?? null;
            if (filled($value)) {
                $payload[$key] = (string) $value;
            }
        }

        return array_filter(
            $payload,
            static fn (mixed $value, string $key): bool => $key === 'target' || ($value !== null && $value !== ''),
            ARRAY_FILTER_USE_BOTH
        );
    }

    protected function hrefToPublicDiskPath(?string $href): ?string
    {
        if (blank($href)) {
            return null;
        }

        $urlPath = parse_url($href, PHP_URL_PATH);
        if (! is_string($urlPath)) {
            return null;
        }

        if (! str_contains($urlPath, '/storage/')) {
            return null;
        }

        return ltrim(str($urlPath)->after('/storage/')->toString(), '/');
    }
}
