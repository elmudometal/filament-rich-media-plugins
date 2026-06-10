<?php

namespace ElmudoDev\FilamentRichMediaPlugins\Commands;

use Illuminate\Console\Command;

class FilamentRichMediaPluginsCommand extends Command
{
    public $signature = 'filament-rich-media-plugins';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
