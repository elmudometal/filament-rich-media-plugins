<?php

namespace ElmudoDev\FilamentRichMediaPlugins\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ElmudoDev\FilamentRichMediaPlugins\FilamentRichMediaPlugins
 */
class FilamentRichMediaPlugins extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ElmudoDev\FilamentRichMediaPlugins\FilamentRichMediaPlugins::class;
    }
}
