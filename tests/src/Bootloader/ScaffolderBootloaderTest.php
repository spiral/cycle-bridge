<?php

declare(strict_types=1);

namespace Spiral\Tests\Bootloader;

use Spiral\Cycle\Scaffolder\Declaration;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Tests\BaseTest;

final class ScaffolderBootloaderTest extends BaseTest
{
    public function testDeclarationsShouldBeAdded(): void
    {
        $config = $this->getConfig(ScaffolderConfig::CONFIG);

        $this->assertIsArray($config);
        $this->assertIsArray($config['declarations']);
        $this->assertIsArray($config['declarations']['migration']);
        $this->assertIsArray($config['declarations']['entity']);
        $this->assertIsArray($config['declarations']['repository']);

        $this->assertSame(
            [
                'namespace' => '',
                'postfix'   => 'Migration',
                'class'     => Declaration\MigrationDeclaration::class,
            ],
            $config['declarations']['migration']
        );

        $this->assertSame(
            [
                'namespace' => 'Database',
                'postfix'   => '',
                'options'   => [
                    'annotated' => Declaration\Entity\AnnotatedDeclaration::class,
                ],
            ],
            $config['declarations']['entity']
        );

        $this->assertSame(
            [
                'namespace' => 'Repository',
                'postfix'   => 'Repository',
                'class'     => Declaration\RepositoryDeclaration::class,
            ],
            $config['declarations']['repository']
        );
    }
}
