<?php

declare(strict_types=1);

namespace ElmudoDev\FilamentRichMediaPlugins;

use ElmudoDev\FilamentRichMediaPlugins\Extensions\ImageExtension;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Tiptap\Core\Extension;

class ImagePlugin implements RichContentPlugin
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
            new ImageExtension,
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
            FilamentAsset::getScriptSrc('rich-media-plugins/image', 'elmudo-dev/filament-rich-media-plugins'),
        ];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('image')
                ->label(__('rich-media-plugins::image.heading.insert'))
                ->icon(Heroicon::OutlinedPhoto)
                ->action(arguments: '{ src: $getEditor().getAttributes(\'image\')?.src, alt: $getEditor().getAttributes(\'image\')?.alt, width: $getEditor().getAttributes(\'image\')?.width, height: $getEditor().getAttributes(\'image\')?.height, lazy: $getEditor().getAttributes(\'image\')?.lazy, id: $getEditor().getAttributes(\'image\')?.id }')
                ->activeKey('image'),
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
            Action::make('image')
                ->label(__('rich-media-plugins::image.heading.insert'))
                ->modalHeading(fn (array $arguments): string => blank($arguments['src'] ?? null)
                    ? __('rich-media-plugins::image.heading.insert')
                    : __('rich-media-plugins::image.heading.update'))
                ->modalWidth(Width::Medium)
                ->fillForm(fn (array $arguments): array => [
                    'src' => $this->getRelativePathFromUrl($arguments['src'] ?? null),
                    'alt' => $arguments['alt'] ?? '',
                    'width' => $arguments['width'] ?? '',
                    'height' => $arguments['height'] ?? '',
                    'lazy' => ! empty($arguments['lazy']),
                    'type' => $this->isImageSrc($arguments['src'] ?? null) ? 'image' : 'document',
                ])
                ->schema($this->imageFormSchema())
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $this->handleImageAction($arguments, $data, $component);
                }),
        ];
    }

    /**
     * @return array<int, Component>
     */
    protected function imageFormSchema(): array
    {
        /** @var array<string> $acceptedFileTypes */
        $acceptedFileTypes = (array) config('rich-media-image.accepted_file_types', []);

        /** @var int|null $maxSize */
        $maxSize = config('rich-media-image.max_size');

        return [
            FileUpload::make('src')
                ->label(__('rich-media-plugins::image.fields.file'))
                ->disk((string) config('rich-media-image.disk', 'public'))
                ->directory((string) config('rich-media-image.directory', 'userfiles/media'))
                ->visibility('public')
                ->acceptedFileTypes($acceptedFileTypes)
                ->maxFiles(1)
                ->when($maxSize !== null, fn (FileUpload $upload) => $upload->maxSize($maxSize))
                ->required()
                ->live()
                ->afterStateUpdated(function (mixed $state, callable $set): void {
                    if ($state instanceof TemporaryUploadedFile) {
                        $mimeType = $state->getMimeType();

                        if (Str::contains($mimeType, 'image')) {
                            $set('type', 'image');

                            $dimensions = $state->dimensions();
                            if (is_array($dimensions)) {
                                $set('width', (string) $dimensions[0]);
                                $set('height', (string) $dimensions[1]);
                            }
                        } else {
                            $set('type', 'document');
                        }
                    }
                }),
            TextInput::make('link_text')
                ->label(__('rich-media-plugins::image.fields.link_text'))
                ->required()
                ->visible(fn (callable $get): bool => ($get('type') ?? 'image') === 'document'),
            TextInput::make('alt')
                ->label(__('rich-media-plugins::image.fields.alt'))
                ->hidden(fn (callable $get): bool => ($get('type') ?? 'image') === 'document'),
            Checkbox::make('lazy')
                ->label(__('rich-media-plugins::image.fields.lazy'))
                ->default(false)
                ->hidden(fn (callable $get): bool => ($get('type') ?? 'image') === 'document'),
            Grid::make(2)->schema([
                TextInput::make('width')
                    ->label(__('rich-media-plugins::image.fields.width'))
                    ->hidden(fn (callable $get): bool => ($get('type') ?? 'image') === 'document'),
                TextInput::make('height')
                    ->label(__('rich-media-plugins::image.fields.height'))
                    ->hidden(fn (callable $get): bool => ($get('type') ?? 'image') === 'document'),
            ]),
            Hidden::make('type')
                ->default('image'),
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @param  array<string, mixed>  $data
     */
    protected function handleImageAction(array $arguments, array $data, RichEditor $component): void
    {
        $src = $this->resolveSourceUrl($data);
        if (blank($src)) {
            return;
        }

        $type = $data['type'] ?? 'image';
        $isUpdating = filled($arguments['src'] ?? null);

        if ($type === 'document') {
            $this->insertDocumentLink($src, $data, $arguments, $component);

            return;
        }

        if ($isUpdating) {
            $this->updateExistingImage($src, $data, $arguments, $component);

            return;
        }

        $this->insertNewImage($src, $data, $arguments, $component);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveSourceUrl(array $data): ?string
    {
        /** @var string|null $path */
        $path = $data['src'] ?? null;

        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $disk = (string) config('rich-media-image.disk', 'public');

        return Storage::disk($disk)->url($path);
    }

    protected function getRelativePathFromUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $disk = (string) config('rich-media-image.disk', 'public');
        $storage = Storage::disk($disk);
        $baseUrl = $storage->url('');

        if (filled($baseUrl) && str_starts_with($url, $baseUrl)) {
            return ltrim(substr($url, strlen($baseUrl)), '/');
        }

        $urlPath = parse_url($url, PHP_URL_PATH);
        if (is_string($urlPath) && str_contains($urlPath, '/storage/')) {
            return ltrim(explode('/storage/', $urlPath, 2)[1], '/');
        }

        return $url;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $arguments
     */
    protected function insertNewImage(string $src, array $data, array $arguments, RichEditor $component): void
    {
        /** @var array<string, mixed>  $editorSelection */
        $editorSelection = $arguments['editorSelection'] ?? null;
        $component->runCommands(
            [
                EditorCommand::make('insertContent', arguments: [[
                    'type' => 'image',
                    'attrs' => [
                        'src' => $src,
                        'alt' => $data['alt'] ?? null,
                        'width' => filled($data['width'] ?? null) ? $data['width'] : null,
                        'height' => filled($data['height'] ?? null) ? $data['height'] : null,
                        'lazy' => ! empty($data['lazy']) ? 'true' : null,
                    ],
                ]]),
            ],
            editorSelection: $editorSelection,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $arguments
     */
    protected function updateExistingImage(string $src, array $data, array $arguments, RichEditor $component): void
    {
        /** @var array<string, mixed>  $editorSelection */
        $editorSelection = $arguments['editorSelection'] ?? null;

        if (($editorSelection['type'] ?? null) !== 'node') {
            $editorSelection['type'] = 'node';
            $editorSelection['anchor']--;
            unset($editorSelection['head']);
        }

        $component->runCommands(
            [
                EditorCommand::make('updateAttributes', arguments: [
                    'image',
                    [
                        'src' => $src,
                        'alt' => $data['alt'] ?? null,
                        'width' => filled($data['width'] ?? null) ? $data['width'] : null,
                        'height' => filled($data['height'] ?? null) ? $data['height'] : null,
                        'lazy' => ! empty($data['lazy']) ? 'true' : null,
                    ],
                ]),
            ],
            editorSelection: $editorSelection,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $arguments
     */
    protected function insertDocumentLink(string $src, array $data, array $arguments, RichEditor $component): void
    {
        $linkText = $data['link_text'] ?? basename($src);
        /** @var array<string, mixed>  $editorSelection */
        $editorSelection = $arguments['editorSelection'] ?? null;

        $component->runCommands(
            [
                EditorCommand::make('insertContent', arguments: [[
                    'type' => 'text',
                    'text' => $linkText,
                    'marks' => [
                        [
                            'type' => 'link',
                            'attrs' => [
                                'href' => $src,
                                'target' => '_blank',
                            ],
                        ],
                    ],
                ]]),
            ],
            editorSelection: $editorSelection,
        );
    }

    protected function isImageSrc(?string $src): bool
    {
        if (blank($src)) {
            return true;
        }

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'jxl', 'heic', 'bmp'];
        $urlPath = parse_url($src, PHP_URL_PATH);

        if (! is_string($urlPath)) {
            return true;
        }

        $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));

        return in_array($extension, $imageExtensions, true);
    }
}
