<?php

declare(strict_types=1);

namespace Spiral\Tests\Cycle;

use Cycle\Database\DatabaseInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Mapper\Mapper;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Tests\BaseTest;
use Spiral\Tests\Cycle\Fixture\Bar;

final class EntityManagerTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $db = $this->getContainer()->get(DatabaseInterface::class);

        $bars = $db->table('bars')->getSchema();
        $bars->primary('id');
        $bars->string('name');
        $bars->save();
    }

    public function testEntityManagerStateCanBeSharedAndFinalizeAtOnce(): void
    {
        $orm = $this->getOrm()->with(
            new Schema([
                Bar::class => [
                    SchemaInterface::ROLE => 'bar',
                    SchemaInterface::MAPPER => Mapper::class,
                    SchemaInterface::DATABASE => 'default',
                    SchemaInterface::TABLE => 'bars',
                    SchemaInterface::PRIMARY_KEY => 'id',
                    SchemaInterface::COLUMNS => ['id', 'name'],
                    SchemaInterface::SCHEMA => [],
                    SchemaInterface::RELATIONS => [],
                ],
            ])
        );

        $this->getContainer()->bindSingleton(ORMInterface::class, fn(): ORMInterface => $orm);

        $em = $this->getEntityManager();
        $em->persist(new Bar('foo'));
        $em->run();
        $bar = $this->getRepository(Bar::class)->findOne(['name' => 'foo']);
        self::assertEquals('foo', $bar->name);
        $bar->name = 'baz';
        $em->persist($bar);
        $this->getContainer()->get(FinalizerInterface::class)->finalize();
        $em->run();
        self::assertNull($this->getRepository(Bar::class)->findOne(['name' => 'baz']));
    }

    public function testMethodCleanCalled(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('clean');

        $this->getContainer()->bindSingleton(EntityManagerInterface::class, fn(): EntityManagerInterface => $em);
        $this->getContainer()->get(EntityManagerInterface::class);
        $this->getContainer()->get(FinalizerInterface::class)->finalize();
    }
}
