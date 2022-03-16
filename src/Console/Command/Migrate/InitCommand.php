<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\Migrate;

final class InitCommand extends AbstractCommand
{
    protected const NAME = 'migrate:init';
    protected const DESCRIPTION = 'Init migrations component (create migrations table)';

    /**
     * Perform command.
     */
    public function perform(): int
    {
        $this->migrator->configure();
        $this->writeln('<info>Migrations table were successfully created</info>');

        return self::SUCCESS;
    }
}
