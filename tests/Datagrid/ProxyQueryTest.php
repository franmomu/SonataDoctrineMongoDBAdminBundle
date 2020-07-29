<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Datagrid;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use MongoDB\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\SimpleAnnotationDocument;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\SimpleDocument;

final class ProxyQueryTest extends TestCase
{
    /**
     * @var Builder&MockObject
     */
    private $queryBuilder;

    protected function setUp(): void
    {
        $this->queryBuilder = $this->createMock(Builder::class);
    }

    public function testSetLimitToZeroWhenResettingMaxResults(): void
    {
        $proxyQuery = new ProxyQuery($this->queryBuilder);

        $this->queryBuilder
            ->expects($this->once())
            ->method('limit')
            ->with(0);

        $proxyQuery->setMaxResults(null);

        $this->assertNull($proxyQuery->getMaxResults());
    }

    public function testSetSkipToZeroWhenResettingFirstResult(): void
    {
        $proxyQuery = new ProxyQuery($this->queryBuilder);

        $this->queryBuilder
            ->expects($this->once())
            ->method('skip')
            ->with(0);

        $proxyQuery->setFirstResult(null);

        $this->assertNull($proxyQuery->getFirstResult());
    }

    public function testSorting(): void
    {
        $proxyQuery = new ProxyQuery($this->queryBuilder);
        $proxyQuery->setSortBy([], ['fieldName' => 'name']);
        $proxyQuery->setSortOrder('ASC');

        $this->assertSame(
            'name',
            $proxyQuery->getSortBy()
        );

        $this->assertSame(
            'ASC',
            $proxyQuery->getSortOrder()
        );
    }

    public function testExecute(): void
    {
        $dm = DocumentManager::create(null, $this->createConfiguration());
        $queryBuilder = new Builder($dm, SimpleAnnotationDocument::class);

        $proxyQuery = new ProxyQuery($queryBuilder);
        $proxyQuery->execute();
    }

    private function createConfiguration(): Configuration
    {
        $config = new Configuration();

        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir(sys_get_temp_dir());
        $config->setHydratorNamespace('Hydrators');
        $config->setPersistentCollectionDir(sys_get_temp_dir());
        $config->setPersistentCollectionNamespace('PersistentCollections');
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver());

        return $config;
    }
}
