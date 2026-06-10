<?php

declare(strict_types=1);

test('default link-button config values are set correctly', function () {
    expect(config('rich-media-link-button.disable_link_as_button'))->toBeFalse()
        ->and(config('rich-media-link-button.disk'))->toBe('local')
        ->and(config('rich-media-link-button.directory'))->toBe('userfiles/files/');
});

test('default image config values are set correctly', function () {
    expect(config('rich-media-image.disk'))->toBe('public')
        ->and(config('rich-media-image.directory'))->toBe('userfiles/media')
        ->and(config('rich-media-image.accepted_file_types'))->toBeArray()
        ->and(config('rich-media-image.max_size'))->toBeNull();
});

test('config can be updated dynamically', function () {
    config(['rich-media-link-button.disk' => 'public']);
    expect(config('rich-media-link-button.disk'))->toBe('public');

    config(['rich-media-image.disk' => 's3']);
    expect(config('rich-media-image.disk'))->toBe('s3');
});
