<?php

declare(strict_types=1);

namespace Spiral\Tests\DataGrid;

use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Grid;
use Spiral\DataGrid\GridFactory;
use Spiral\DataGrid\GridSchema;
use Spiral\DataGrid\Input\ArrayInput;
use Spiral\DataGrid\Specification\Pagination\PagePaginator;
use Spiral\DataGrid\Specification\Sorter\Sorter;
use Spiral\Cycle\DataGrid\Writer\QueryWriter;

final class GeneratorTest extends BaseTest
{
    public function setUp(): void
    {
        parent::setUp();

        $users = $this->db->table('users')->getSchema();
        $users->primary('id');
        $users->enum('status', ['active', 'disabled']);
        $users->string('name');
        $users->save();

        $this->db->table('users')->insertMultiple(['status', 'name'], [
            ['active', 'Antony'],
            ['active', 'John'],
            ['disabled', 'Bob'],
        ]);
    }

    public function testSelect(): void
    {
        $this->assertCount(3, $this->db->table('users'));
    }

    public function testBasePaginate(): void
    {
        $schema = new GridSchema();
        $schema->setPaginator(new PagePaginator(1));

        $view = $this->initGenerator()
            ->withInput(new ArrayInput([]))
            ->create(
                $this->db->table('users')->select('*'),
                $schema
            );

        $this->assertEquals([
            [
                'id' => 1,
                'status' => 'active',
                'name' => 'Antony',
            ],
        ], iterator_to_array($view));

        $this->assertNull($view->getOption(Grid::COUNT));

        $this->assertSame([
            'limit' => 1,
            'page' => 1,
        ], $view->getOption(Grid::PAGINATOR));
    }

    public function testPaginateWithCount(): void
    {
        $schema = new GridSchema();
        $schema->setPaginator(new PagePaginator(1));

        $view = $this
            ->initGenerator()
            ->withInput(
                new ArrayInput([
                    GridFactory::KEY_PAGINATE => ['page' => 2],
                    GridFactory::KEY_FETCH_COUNT => true,
                ])
            )
            ->create(
                $this->db->table('users')->select('*'),
                $schema
            );

        $this->assertEquals([
            [
                'id' => 2,
                'status' => 'active',
                'name' => 'John',
            ],
        ], iterator_to_array($view));

        $this->assertSame(3, $view->getOption(Grid::COUNT));

        $this->assertSame([
            'limit' => 1,
            'page' => 2,
        ], $view->getOption(Grid::PAGINATOR));
    }

    public function testDefaultWithMapping(): void
    {
        $schema = new GridSchema();
        $schema->setPaginator(new PagePaginator(1));

        $view = $this
            ->initGenerator()
            ->withInput(
                new ArrayInput([
                    GridFactory::KEY_PAGINATE => ['page' => 2],
                ])
            )
            ->withDefaults([
                GridFactory::KEY_FETCH_COUNT => true,
            ])
            ->create(
                $this->db->table('users')->select('*'),
                $schema
            )
            ->withView(static function ($u) {
                return $u['name'];
            });

        $this->assertEquals([
            'John',
        ], iterator_to_array($view));

        $this->assertSame(3, $view->getOption(Grid::COUNT));

        $this->assertSame([
            'limit' => 1,
            'page' => 2,
        ], $view->getOption(Grid::PAGINATOR));
    }

    public function testSort(): void
    {
        $schema = new GridSchema();
        $schema->addSorter('id', new Sorter('id'));

        $view = $this
            ->initGenerator()
            ->withDefaults([
                GridFactory::KEY_SORT => ['id' => 'desc'],
            ])
            ->create(
                $this->db->table('users')->select('*'),
                $schema
            )
            ->withView(static function ($u) {
                return $u['name'];
            });

        $this->assertEquals([
            'Bob',
            'John',
            'Antony',
        ], iterator_to_array($view));

        $this->assertSame([
            'id' => 'desc',
        ], $view->getOption(Grid::SORTERS));
    }

    public function testSortAsc(): void
    {
        $schema = new GridSchema();
        $schema->addSorter('id', new Sorter('id'));

        $view = $this
            ->initGenerator()
            ->withDefaults([
                GridFactory::KEY_SORT => ['id' => 1],
            ])
            ->create(
                $this->db->table('users')->select('*'),
                $schema
            )
            ->withView(static function ($u) {
                return $u['name'];
            });

        $this->assertEquals([
            'Antony',
            'John',
            'Bob',
        ], iterator_to_array($view));

        $this->assertSame([
            'id' => 'asc',
        ], $view->getOption(Grid::SORTERS));
    }

    public function testSortUnknown(): void
    {
        $schema = new GridSchema();
        $schema->addSorter('id', new Sorter('id'));

        $view = $this
            ->initGenerator()
            ->withDefaults([
                GridFactory::KEY_SORT => ['id' => 2],
            ])
            ->create(
                $this->db->table('users')->select('*'),
                $schema
            )
            ->withView(static function ($u) {
                return $u['name'];
            });

        $this->assertSame([], $view->getOption(Grid::SORTERS));
    }

    private function initCompiler(): Compiler
    {
        $compiler = new Compiler();
        $compiler->addWriter(new QueryWriter());

        return $compiler;
    }

    private function initGenerator(): GridFactory
    {
        return new GridFactory($this->initCompiler());
    }
}
