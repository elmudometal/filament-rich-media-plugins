<?php

declare(strict_types=1);

use ElmudoDev\FilamentRichMediaPlugins\Extensions\ImageExtension;

test('extension name is image', function () {
    expect(ImageExtension::$name)->toBe('image');
});

test('extension adds lazy and id attributes', function () {
    $extension = new ImageExtension;
    $attributes = $extension->addAttributes();

    expect($attributes)->toHaveKeys(['lazy', 'id']);
});

test('extension renderHTML outputs img tag', function () {
    $extension = new ImageExtension;

    $node = new stdClass;
    $node->attrs = (object) [
        'src' => 'https://example.com/image.png',
        'alt' => 'Test image',
    ];

    $htmlAttributes = [
        'src' => 'https://example.com/image.png',
        'alt' => 'Test image',
    ];

    $result = $extension->renderHTML($node, $htmlAttributes);

    expect($result)->toBeArray()
        ->and($result[0])->toBe('img')
        ->and($result[1]['src'])->toBe('https://example.com/image.png')
        ->and($result[1]['alt'])->toBe('Test image');
});
