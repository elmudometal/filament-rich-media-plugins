<?php

declare(strict_types=1);

namespace ElmudoDev\FilamentRichMediaPlugins\Extensions;

use Tiptap\Nodes\Image;
use Tiptap\Utils\HTML;

class ImageExtension extends Image
{
    /**
     * @var string
     */
    public static $name = 'image';

    /**
     * @return array<string, mixed>
     */
    public function addAttributes(): array
    {
        return [
            ...parent::addAttributes(),
            'id' => [],
            'lazy' => [
                'parseHTML' => fn ($DOMNode): ?string => $DOMNode->getAttribute('loading') === 'lazy'
                    ? ($DOMNode->getAttribute('data-lazy') ?: 'true')
                    : null,
                'renderHTML' => fn (object $attributes): array => ! empty($attributes->lazy)
                    ? ['data-lazy' => $attributes->lazy, 'loading' => 'lazy']
                    : [],
            ],
        ];
    }

    /**
     * @param  object  $node
     * @param  array<string, mixed>  $HTMLAttributes
     * @return array<int, mixed>
     */
    public function renderHTML($node, $HTMLAttributes = []): array
    {
        return ['img', HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes), 0];
    }
}
