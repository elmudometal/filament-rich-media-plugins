<?php

declare(strict_types=1);

use ElmudoDev\FilamentRichMediaPlugins\Extensions\LinkButtonExtension;

test('extension name is linkButton', function () {
    expect(LinkButtonExtension::$name)->toBe('linkButton');
});

test('extension parseHTML specifies correct tag', function () {
    $extension = new LinkButtonExtension;
    $parse = $extension->parseHTML();

    expect($parse)->toBeArray()
        ->and(count($parse))->toBe(1)
        ->and($parse[0]['tag'])->toBe('a[data-as-button="true"]');
});

test('extension adds expected attributes', function () {
    $extension = new LinkButtonExtension;
    $attributes = $extension->addAttributes();

    expect($attributes)->toHaveKeys([
        'href', 'target', 'rel', 'id', 'hreflang', 'referrerpolicy', 'class',
        'dataAsButton', 'dataButtonTheme', 'dataLinkSource',
    ]);
});

test('extension sanitizes disallowed URIs', function () {
    $extension = new LinkButtonExtension;

    // Valid URIs
    expect($extension->isAllowedUri('https://example.com'))->toBeTrue()
        ->and($extension->isAllowedUri('mailto:test@example.com'))->toBeTrue()
        ->and($extension->isAllowedUri('tel:+1234567890'))->toBeTrue();

    // Disallowed/malicious URI protocol (javascript)
    expect($extension->isAllowedUri('javascript:alert(1)'))->toBeFalse();
});

test('extension renderHTML applies classes and attributes correctly', function () {
    $extension = new LinkButtonExtension;

    // Mock the TipTap mark object
    $mark = new stdClass;
    $mark->attrs = (object) [
        'href' => 'https://example.com',
        'target' => '_blank',
    ];

    $htmlAttributes = [
        'href' => 'https://example.com',
        'target' => '_blank',
        'data-button-theme' => 'secondary',
        'class' => 'my-custom-class',
    ];

    $result = $extension->renderHTML($mark, $htmlAttributes);

    expect($result)->toBeArray()
        ->and(count($result))->toBe(3)
        ->and($result[0])->toBe('a')
        ->and($result[2])->toBe(0);

    $attributes = $result[1];
    expect($attributes['href'])->toBe('https://example.com')
        ->and($attributes['target'])->toBe('_blank')
        ->and($attributes['data-as-button'])->toBe('true')
        ->and($attributes['class'])->toContain('btn')
        ->and($attributes['class'])->toContain('btn-secondary')
        ->and($attributes['class'])->toContain('my-custom-class');
});
