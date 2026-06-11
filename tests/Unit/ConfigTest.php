<?php

declare(strict_types=1);

test('default link-button config values are set correctly', function () {
    expect(config('rich-media-link-button.disable_link_as_button'))->toBeFalse()
        ->and(config('rich-media-link-button.directory'))->toBe('userfiles/files/');
});

test('default image config values are set correctly', function () {
    expect(config('rich-media-image.directory'))->toBe('userfiles/media')
        ->and(config('rich-media-image.accepted_file_types'))->toBeArray()
        ->and(config('rich-media-image.max_size'))->toBeNull();
});

test('config can be updated dynamically', function () {
    config(['rich-media-link-button.directory' => 'custom/files/']);
    expect(config('rich-media-link-button.directory'))->toBe('custom/files/');

    config(['rich-media-image.directory' => 'custom/media']);
    expect(config('rich-media-image.directory'))->toBe('custom/media');
});
