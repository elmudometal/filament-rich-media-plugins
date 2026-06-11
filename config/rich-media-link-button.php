<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Ocultar opción "Mostrar como botón"
    |--------------------------------------------------------------------------
    |
    | Si es true, el interruptor as_button no se muestra en el modal del enlace.
    |
    */
    'disable_link_as_button' => (bool) env('RICH_EDITOR_DISABLE_LINK_AS_BUTTON', false),

    'directory' => (string) env('RICH_EDITOR_DIRECTORY', 'userfiles/files/'),
];
