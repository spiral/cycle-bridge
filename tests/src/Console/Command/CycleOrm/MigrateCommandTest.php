<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Command\CycleOrm;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Spiral\Files\Files;
use Spiral\Tests\ConsoleTestCase;

final class MigrateCommandTest extends ConsoleTestCase
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

        $this->runCommandDebug('migrate:init', ['-vvv' => true]);
    }

    public function testMigrate(): void
    {
        $output = $this->runCommandDebug('cycle:migrate');

        foreach (static::USER_MIGRATION as $action) {
            $this->assertStringContainsString($action, $output);
        }

        $output = $this->runCommandDebug('cycle:migrate');
        $this->assertStringContainsString('Outstanding migrations found', $output);
    }

    public function testMigrateNoChanges(): void
    {
        $output = $this->runCommandDebug('cycle:migrate');
        foreach (static::USER_MIGRATION as $action) {
            $this->assertStringContainsString($action, $output);
        }

        $this->runCommand('migrate');

        $output = $this->runCommandDebug('cycle:migrate');
        $this->assertStringContainsString('no database changes', $output);
    }

    public function testMigrationShouldBeCreatedWhenNewEntityAppeared()
    {
        $output = $this->runCommandDebug('cycle:migrate', ['-r' => true]);
        foreach (static::USER_MIGRATION as $action) {
            $this->assertStringContainsString($action, $output);
        }

        $fs = new Files();

        $entityPatch = __DIR__.'/../../../../App/Entities/Tag.php';
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

        $output = $this->runCommandDebug('cycle:migrate', ['-r' => true]);

        $tagOutput = [
            'default.tags',
            'create table',
            'add column id',
        ];

        foreach ($tagOutput as $action) {
            $this->assertStringContainsString($action, $output);
        }

        $fs->delete($entityPatch);
    }
}
