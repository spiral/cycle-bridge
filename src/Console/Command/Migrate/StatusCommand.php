<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\Migrate;

use Cycle\Migrations\State;

/**
 * Get list of all available migrations and their statuses.
 */
final class StatusCommand extends AbstractCommand
{
    protected const NAME = 'migrate:status';
    protected const DESCRIPTION = 'Get list of all available migrations and their statuses.';
    protected const PENDING = '<fg=red>not executed yet</fg=red>';

    public function perform(): int
    {
        $this->migrator->configure();

        if (empty($this->migrator->getMigrations())) {
            $this->writeln('<comment>No migrations were found.</comment>');

            return self::SUCCESS;
        }

        $table = $this->table(['Migration', 'Created at', 'Executed at']);
        foreach ($this->migrator->getMigrations() as $migration) {
            $state = $migration->getState();

            $table->addRow(
                [
                    $state->getName(),
                    $state->getTimeCreated()->format('Y-m-d H:i:s'),
                    $state->getStatus() == State::STATUS_PENDING
                        ? self::PENDING
                        : '<info>' . $state->getTimeExecuted()->format('Y-m-d H:i:s') . '</info>',
                ],
            );
        }

        $table->render();

        return self::SUCCESS;
    }
}
