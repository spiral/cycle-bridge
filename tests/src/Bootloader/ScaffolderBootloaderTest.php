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

        $declarations = $config['defaults']['declarations'] ?? $config['declarations'];

        $this->assertIsArray($declarations);
        $this->assertIsArray($declarations['migration']);
        $this->assertIsArray($declarations['entity']);
        $this->assertIsArray($declarations['repository']);

        $this->assertSame(
            [
                'namespace' => '',
                'postfix'   => 'Migration',
                'class'     => Declaration\MigrationDeclaration::class,
            ],
            $declarations['migration']
        );

        $this->assertSame(
            [
                'namespace' => 'Database',
                'postfix'   => '',
                'options'   => [
                    'annotated' => Declaration\Entity\AnnotatedDeclaration::class,
                ],
            ],
            $declarations['entity']
        );

        $this->assertSame(
            [
                'namespace' => 'Repository',
                'postfix'   => 'Repository',
                'class'     => Declaration\RepositoryDeclaration::class,
            ],
            $declarations['repository']
        );
    }
}
