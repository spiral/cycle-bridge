<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Spiral\Cycle\Annotated\Locator\ListenerEntityLocator;
use Spiral\Files\Files;
use Spiral\Tests\ConsoleTest;

final class MigrateCommandTest extends ConsoleTest
{
    public const ENV = [
        'SAFE_MIGRATIONS' => true,
        'USE_MIGRATIONS' => true,
    ];

    public const USER_MIGRATION = [
        'default.users',
        'create table',
        'add column id',
        'add column user_id',
        'add column name',
        'add index on [user_id]',
        'add foreign key on user_id',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->runCommand('migrate:init', ['-vvv' => true]);
    }

    public function testMigrate(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('cycle:migrate', [], self::USER_MIGRATION);
        $this->assertConsoleCommandOutputContainsStrings('cycle:migrate', [], 'Outstanding migrations found');
    }

    public function testMigrateNoChanges(): void
    {
        $this->runCommand('cycle:migrate');
        $this->runCommand('migrate');
        $this->assertConsoleCommandOutputContainsStrings('cycle:migrate', [], 'no database changes');
    }

    public function testMigrationShouldBeCreatedWhenNewEntityAppeared(): void
    {
        $this->assertConsoleCommandOutputContainsStrings('cycle:migrate', ['-r' => true], self::USER_MIGRATION);

        $fs = new Files();

        $entityPatch = __DIR__.'/../../../../app/Entities/Tag.php';
        file_put_contents(
            $entityPatch, <<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Spiral\App\Entities;

                use Cycle\Annotated\Annotation\Column;
                use Cycle\Annotated\Annotation\Entity;

                #[Entity]
                class Tag
                {
                    #[Column(type: 'primary')]
                    public int $id;
                }
                PHP
        );

        // This is the important part. Listeners only work when the app is bootstrapping.
        $listener = $this->getContainer()->get(ListenerEntityLocator::class);
        $listener->listen(new \ReflectionClass(\Spiral\App\Entities\Tag::class));

        $this->assertConsoleCommandOutputContainsStrings('cycle:migrate', ['-r' => true], [
            'default.tags',
            'create table',
            'add column id',
        ]);

        $fs->delete($entityPatch);
    }
}
