<?php

declare(strict_types=1);

namespace Spiral\Tests;

use Spiral\DatabaseSeeder\Database\Traits\DatabaseAsserts;
use Spiral\DatabaseSeeder\Database\Traits\Helper;
use Spiral\DatabaseSeeder\Database\Traits\ShowQueries;
use Spiral\DatabaseSeeder\Database\Traits\Transactions;

abstract class DatabaseTest extends BaseTest
{
    use Transactions, Helper, DatabaseAsserts, ShowQueries;

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cleanIdentityMap();
        $this->getCurrentDatabaseDriver()->disconnect();
    }

    public function persist(object ...$entity): void
    {
        $em = $this->getEntityManager();
        foreach ($entity as $e) {
            $em->persist($e);
        }
        $em->run();
    }

    /**
     * @template T of object
     * @param T $entity
     * @return T
     */
    public function refreshEntity(object $entity, string $pkField = 'uuid'): object
    {
        return $this->getRepositoryFor($entity)->findByPK($entity->{$pkField});
    }
}
