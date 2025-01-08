<?php

declare(strict_types=1);

namespace Spiral\Cycle\Console\Command\CycleOrm;

use Cycle\Schema\Provider\SchemaProviderInterface;
use Spiral\Console\Command;

final class UpdateCommand extends Command
{
    protected const NAME = 'cycle';
    protected const DESCRIPTION = 'Update (init) cycle schema from database and annotated classes';

    public function perform(SchemaProviderInterface $schemaProvider): int
    {
        $this->info('Updating ORM schema... ');

        $schemaProvider->read();

        $this->info('Schema has been updated.');

        return self::SUCCESS;
    }
}
