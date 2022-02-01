<?php

declare(strict_types=1);

namespace Spiral\Tests\DataGrid;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\Query\SelectQuery;
use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\SpecificationInterface;
use Spiral\Cycle\DataGrid\Writer\QueryWriter;

abstract class BaseTest extends \Spiral\Tests\BaseTest
{
    protected DatabaseInterface $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = $this->app->get(DatabaseInterface::class);
    }


    protected function initQuery(): SelectQuery
    {
        return $this->db->select()->from('users');
    }

    protected function compile($source, SpecificationInterface ...$specifications)
    {
        $compiler = new Compiler();
        $compiler->addWriter(new QueryWriter());

        return $compiler->compile($source, ...$specifications);
    }

    protected function assertEqualSQL(string $expected, SelectQuery $compiled): void
    {
        $this->assertSame(
            preg_replace("/\s+/", '', $expected),
            preg_replace("/\s+/", '', (string)$compiled)
        );
    }
}
