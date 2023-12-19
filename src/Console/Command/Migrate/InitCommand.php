<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\Migrate;

final class InitCommand extends AbstractCommand
{
    protected const NAME = 'migrate:init';
    protected const DESCRIPTION = 'Create migrations table if not exists.';

    /**
     * Perform command.
     */
    public function perform(): int
    {
        $this->migrator->configure();
        $this->info('Migration table was successfully created.');

        return self::SUCCESS;
    }
}
