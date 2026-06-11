<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Directorio dentro del disco
    |--------------------------------------------------------------------------
    */
    'directory' => (string) env('RICH_EDITOR_MEDIA_DIRECTORY', 'userfiles/media'),

    /*
    |--------------------------------------------------------------------------
    | Tipos de archivo aceptados
    |--------------------------------------------------------------------------
    |
    | Lista de MIME types permitidos en el upload del modal de media.
    |
    */
    'accepted_file_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'image/avif',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tamaño máximo (KB)
    |--------------------------------------------------------------------------
    |
    | null = usa el valor por defecto del sistema.
    |
    */
    'max_size' => null,
];
