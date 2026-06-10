<?php

declare(strict_types=1);

namespace ElmudoDev\FilamentRichMediaPlugins\Extensions;

use Tiptap\Core\Mark;
use Tiptap\Utils\HTML;

class LinkButtonExtension extends Mark
{
    /**
     * @var string
     */
    public static $name = 'linkButton';

    /**
     * @return array<string, mixed>
     */
    public function addOptions(): array
    {
        return [
            'HTMLAttributes' => [],
            'allowedProtocols' => [
                'http', 'https', 'ftp', 'ftps', 'mailto', 'tel', 'callto', 'sms', 'cid', 'xmpp',
            ],
            'isAllowedUri' => fn (string $uri): bool => $this->isAllowedUri($uri),
        ];
    }

    public function isAllowedUri(?string $uri): bool
    {
        if ($uri === null || $uri === '') {
            return true;
        }

        /** @var string $sanitised */
        $sanitised = preg_replace('/[\x00-\x20\x{00A0}\x{1680}\x{180E}\x{2000}-\x{2029}\x{205F}\x{3000}]/u', '', $uri);

        $pattern = '/^(?:(?:'.implode('|', array_map('preg_quote', $this->options['allowedProtocols']))
        .'):|[^a-z]|[a-z0-9+.\-]+(?:[^a-z+.\-:]|$))/i';

        return (bool) preg_match($pattern, $sanitised);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parseHTML(): array
    {
        return [
            [
                'tag' => 'a[data-as-button="true"]',
                'priority' => 1001,
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function addAttributes(): array
    {
        return [
            'href' => [],
            'target' => [],
            'rel' => [],
            'id' => [],
            'hreflang' => [],
            'referrerpolicy' => [],
            'class' => [],
            'dataAsButton' => [
                'parseHTML' => fn ($DOMNode): ?string => $DOMNode->getAttribute('data-as-button') ?: null,
                'renderHTML' => fn (object $attributes): array => isset($attributes->dataAsButton) ? ['data-as-button' => $attributes->dataAsButton] : [],
            ],
            'dataButtonTheme' => [
                'parseHTML' => fn ($DOMNode): ?string => $DOMNode->getAttribute('data-button-theme') ?: null,
                'renderHTML' => fn (object $attributes): array => isset($attributes->dataButtonTheme) ? ['data-button-theme' => $attributes->dataButtonTheme] : [],
            ],
            'dataLinkSource' => [
                'parseHTML' => fn ($DOMNode): ?string => $DOMNode->getAttribute('data-link-source') ?: null,
                'renderHTML' => fn (object $attributes): array => isset($attributes->dataLinkSource) ? ['data-link-source' => $attributes->dataLinkSource] : [],
            ],
        ];
    }

    /**
     * @param  object  $mark
     * @param  array<string, mixed>  $HTMLAttributes
     * @return array<int, mixed>
     */
    public function renderHTML($mark, $HTMLAttributes = []): array
    {
        $href = $HTMLAttributes['href'] ?? '';
        $isAllowed = $this->options['isAllowedUri']($href);

        if (! $isAllowed) {
            $HTMLAttributes['href'] = '';
        }

        $attributes = HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes);

        if (isset($mark->attrs)) {
            foreach ((array) $mark->attrs as $key => $value) {
                if ($value === null) {
                    unset($attributes[$key]);
                }
            }
        }

        $dataButtonTheme = $attributes['data-button-theme'] ?? 'primary';

        $existingClass = $attributes['class'] ?? '';
        $classes = array_filter(
            explode(' ', $existingClass),
            fn ($cls) => $cls !== 'btn' && ! str_starts_with($cls, 'btn-') && $cls !== ''
        );

        $classes[] = 'btn';
        $classes[] = "btn-{$dataButtonTheme}";
        $attributes['class'] = implode(' ', array_unique($classes));
        $attributes['data-as-button'] = 'true';

        return [
            'a',
            $attributes,
            0,
        ];
    }
}
