<?php

namespace Wsmallnews\User\Commands;

use Illuminate\Console\Command;

class UserCommand extends Command
{
    public $signature = 'user';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
